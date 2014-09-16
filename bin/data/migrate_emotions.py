#!/usr/bin/python
import MySQLdb
import json
import sys

mysqlHostname = ''
mysqlUsername = 'selfieclub'
mysqlPassword = ''
mysqlDatabase = 'hotornot-dev'

wdb = MySQLdb.connect(host=mysqlHostname,
                      user=mysqlUsername,
                      passwd=mysqlPassword,
                      db=mysqlDatabase)

rdb = MySQLdb.connect(host=mysqlHostname,
                      user=mysqlUsername,
                      passwd=mysqlPassword,
                      db=mysqlDatabase)

wcursor = wdb.cursor()
rcursor = rdb.cursor()
rcursor.execute('''
select challenge_id, tbl_emotion.id
  from tblChallengeSubjectMap, tblChallengeSubjects, tbl_emotion, tblChallenges
 where tblChallengeSubjectMap.subject_id=tblChallengeSubjects.id
   and tblChallengeSubjects.title = tbl_emotion.title
   and challenge_id=tblChallenges.id
   and tblChallenges.added > "2014-07-01"
       order by challenge_id asc''')
numrows = rcursor.rowcount
print numrows, 'subjects found to convert'

currentChallengeId = 0
currentSubjects = []
for row in rcursor:
    challengeId = row[0]
    subjectId = row[1]
    if challengeId == currentChallengeId:
        currentSubjects.append(subjectId)
    else:
        numSubjects = len(currentSubjects)
        if numSubjects > 0:
            subjectsJson = json.dumps(currentSubjects)
            try:
                wcursor.execute('''
insert into tbl_status_update_emotion
       (status_update_id, emotion_id_count, emotion_id_json)
values (%s, %s, %s)''', (currentChallengeId, numSubjects, subjectsJson))
                wdb.commit()
                sys.stdout.write('.')
            except:
                wdb.rollback()
                sys.stdout.write('*')
        currentChallengeId = challengeId
        currentSubjects = [subjectId]

print 'done.'
rcursor.close()
rdb.close()
wcursor.close()
wdb.close()
