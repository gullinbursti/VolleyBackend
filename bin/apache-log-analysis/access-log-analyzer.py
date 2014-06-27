#! /usr/bin/env python

import sys
import re
from pprint import pprint
from datetime import datetime

PROGRESS_BATCH_COUNT = 100
APACHE_ACCESS_LOG_REGEX=r"""
    ^(\S+)\s                    # IP
    (\S+)\s+                    # remote logname
    (\S+)\s+                    # remote user
    \[([^]]+)\]\s               # date
    "(\S*)\s?                   # method
    (?:
        #((?:[^"]*(?:\\")?)*)\s  # URL
        (\S+)\s                 # URL
        (\S+)"                  # protocol
        |                       #     OR
        (\S+)"                  # possibly URL with no protocol
    )
    \s(\S+)                     # status code
    \s(\S+)                     # bytes
    (?:
        \s"([^"]*)"             # referrer
        \s"([^"]*)"             # user agent
        |                       #     OR
                                # NOTHING
    )$
"""

def logLines(fileNames):
    for fileName in fileNames:
        line_count = 0
        sys.stdout.write("Processing '{}': ".format(fileName))
        fileHandle = open(fileName, 'r')
        for line in fileHandle:
            line_count = line_count + 1
            if (line_count % PROGRESS_BATCH_COUNT) == 0:
                sys.stdout.write('.')
                sys.stdout.flush()
            yield line.strip()
        print("  (processed {} lines in '{}')\n".format(line_count, fileName))
        fileHandle.close()

def logLineParser(lines):
    regex = re.compile(APACHE_ACCESS_LOG_REGEX, re.VERBOSE)
    for line in lines:
        match = regex.match(line)
        if match:
            try:
                yield processMatch(match)
            except ValueError:
                print( "\nERROR - Unable to convert: {}".format(line) )
        else:
            print( "\nERROR - Unable to parse: {}".format(line) )

def filterHttpOptionsMethods(entries):
    for entry in entries:
        if entry['method'] != 'OPTIONS':
            yield entry

def filterNon200StatusCode(entries):
    for entry in entries:
        if entry['status_code'] == 200:
            yield entry

def dicectSc000xCalls(entries):
    is_sc000x = re.compile(r"^\/(sc000[0-9])")
    dicectSc000x = re.compile(r"^\/(sc000[0-9])\/([^\/]+)(?:(?:\/([^\/]+))|(.*))$")
    for entry in entries:
        url = entry['url']
        sc000x = None
        is_sc000x_match = is_sc000x.match(url)
        if is_sc000x_match:
            match = dicectSc000x.match(url)
            if match:
                other = match.group(4) if match.group(4) != '' else None
                sc000x = {
                        'version': match.group(1),
                        'endpoint': match.group(2),
                        'action': match.group(3),
                        'other': other
                }
            else:
                print("\nERROR - Unable to parse sc000X: {}".format(url))
        entry['sc000x'] = sc000x
        yield entry;

def convertApacheTimestamp(entries):
    for entry in entries:
        timestamp = entry['date']
        entry['datetime'] = datetime.strptime(timestamp[:-6], '%d/%b/%Y:%H:%M:%S')
        yield entry

def processMatch(match):
    url = match.group(6) if match.group(6) is not None else match.group(8)
    return {
        'raw_line': match.group(0),
        'ip': match.group(1),
        'remote_logname': match.group(2),
        'remote_user': match.group(3),
        'date': match.group(4),
        'method': match.group(5),
        'url': url,
        'protocol': match.group(7),
        'status_code': int(match.group(9)),
        'bytes': int(match.group(10)),
        'referrer': match.group(11),
        'user_agent': match.group(12)
    }

class CounterBase(object):
    def getKeys(self):
        return self.counters.keys()

    def getCount(self, key):
        return self.counters[key]

    def writeCsv(self, fileName):
        out_file = open(fileName, 'w')
        out_file.write("\"Key\",\"Count\"\n")
        for key in self.getKeys():
            out_file.write("\"{}\",{}\n".format(key, self.getCount(key)))
        out_file.close()

class ScVersionCounter(CounterBase):
    def __init__(self):
        self.counters = {};

    def process(self, entry):
        if entry['sc000x'] is not None:
            version = entry['sc000x']['version']
            if version in self.counters:
                self.counters[version] = self.counters[version] + 1
            else:
                self.counters[version] = 1

class ScEndpointCounter(CounterBase):
    def __init__(self):
        self.counters = {};

    def process(self, entry):
        if entry['sc000x'] is not None:
            version = entry['sc000x']['version']
            endpoint = entry['sc000x']['endpoint']
            key = version + "." + endpoint
            if key in self.counters:
                self.counters[key] = self.counters[key] + 1
            else:
                self.counters[key] = 1

class ScActionCounter(CounterBase):
    def __init__(self):
        self.counters = {};

    def process(self, entry):
        if entry['sc000x'] is not None:
            action = entry['sc000x']['action']
            if not action:
                return
            version = entry['sc000x']['version']
            endpoint = entry['sc000x']['endpoint']
            key = version + "." + endpoint + "." + action
            if key in self.counters:
                self.counters[key] = self.counters[key] + 1
            else:
                self.counters[key] = 1


def main():
    if len(sys.argv) <= 1:
        print("Usage: {} FILE_1 [... FILE_N]".format(sys.argv[0]))

    log_lines = logLines(sys.argv[1:])
    log_parser = logLineParser(log_lines)
    option_filtered = filterHttpOptionsMethods(log_parser)
    non_200_filtered = filterNon200StatusCode(option_filtered)
    sc000x = dicectSc000xCalls(non_200_filtered)
    converted_timestamp = convertApacheTimestamp(sc000x)

    final_reader = converted_timestamp

    scVersionCounter = ScVersionCounter()
    scEndpointCounter = ScEndpointCounter()
    scActionCounter = ScActionCounter()

    for entry in final_reader:
        #print( "=" * 79 );
        #print("{} - {}".format(entry['datetime'], entry['url']))
        #pprint(entry)
        scVersionCounter.process(entry)
        scEndpointCounter.process(entry)
        scActionCounter.process(entry)

    scVersionCounter.writeCsv('version.csv')
    scEndpointCounter.writeCsv('endpoint.csv')
    scActionCounter.writeCsv('action.csv')

    return 0

if __name__ == "__main__":
    sys.exit(main())

