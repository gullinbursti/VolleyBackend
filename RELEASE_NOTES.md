# Release Notes

## SC0005

- Update database schema changes:

        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-00.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-01.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-02.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-03.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-04.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-05.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-06.sql

- Update `clubSmsInviteMsg()` in `classes/BIM/Config/Dynamic.php`:

        public static function clubSmsInviteMsg(){
            return "[USERNAME] has invited you to receive their status updates. Tap here >> Taps.io/selfieclub";
        }

- Add `nexmo()` to `classes/BIM/Config/Dynamic.php`.  Make sure to replace `CHANGE_ME` with the correct values:

        public static function nexmo() {
            return (object) array(
                'apiKey' => 'CHANGE_ME',
                'apiSecret' => 'CHANGE_ME',
                'from' => '19189620405',
                'twoFactorJsonEndpoint' => 'https://rest.nexmo.com/sc/us/2fa/json',
                'sendSmsEndpoint' => 'https://rest.nexmo.com/sms/json'
            );
        }




