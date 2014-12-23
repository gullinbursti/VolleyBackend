# Release Notes

## SC0012

- Apply database schema changes:
 - Add `tags` to `club`:

        ```
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0012-00.sql
        ```


## SC0011

- Nothing to do here...


## SC0010

- Apply database schema changes:
 - Add `lat` and `lon` to `club`:

        ```
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0010-00.sql
        ```


## SC0009

- Apply database schema changes: 
 - Create `tbl_sku` and update `tblUsers`:

        ```
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0009-00.sql
        ```


## SC0008

- Apply database schema changes: 
 - Rename `moji_invite` to `tbl_moji_invite`:

       ```
       mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0008-00.sql
       ```

 - Alter `tblChallenges` to add the `parent_id` column:

        ```
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0008-01.sql
        ```

 - Create `tbl_status_update_voter`:

        ```
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0008-02.sql
        ```


## SC0007

- Update database schema changes: 

        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0007-00.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0007-01.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0007-02.sql

- Migrate data (after setting hostname and password inside script):

        python VolleyBackend/bin/data/migrate_emotions.py


## SC0006

- Add `eventDispatcher()` to `classes/BIM/Config/Dynamic.php`.  Make sure to replace `CHANGE_ME` with the correct values:

        public static function eventDispatcher() {
            return (object) array(
                'enabled' => true,
                'celery' => (object) array(
                    'host' => 'CHANGE_ME',
                    'user' => 'CHANGE_ME',
                    'password' => 'CHANGE_ME',
                    'virtual_host' => '/'
                ),
            );
        }


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




