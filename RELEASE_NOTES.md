# Release Notes

## SC0005

- Update database schema changes:

        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-00.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-01.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-02.sql
        mysql -u root hotornot-dev < VolleyBackend/bin/data_schemas/hotornot-dev-schema-sc0005-03.sql

- Update `clubSmsInviteMsg()` in `classes/BIM/Config/Dynamic.php`:

        public static function clubSmsInviteMsg(){
            return "[USERNAME] has invited you to receive their status updates. Tap here >> Taps.io/selfieclub";
        }


