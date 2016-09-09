# express_lims

## TODO

- [ ] front-end for adding / removing tables
- [ ] front-end for editing table fields
- [ ] modal for editing field info
- [ ] batch insert/edit/delete
- [x] button for deleting record
- [ ] business logic for tracking history
- [x] add field type to Field class (e.g. Timestamp, Int, varchar)
- [x] filtering view by clicking on item
- [ ] add extra fields to user meta (last login, number of logins, payment status...)
- [ ] add popover as tooltips to things like add table fields
- [ ] add IE shim for form validation
- [ ] create user types (e.g. admin, read only)
- [ ] create user profile front end for editing name ...


# NOTES

it is assumed that each person that logs in has an associated 'company'
stored in the user_meta table.  this is used to check a subset of all
tables within the EL database

all express lims data is stored in (a single) database _EL

each company will have data tables prepended with their company name e.g. matatu_ (always all lowercase)

each generated table will have an accompanying history table

### Data table
- should at least have a timestamp field and a 'User' field

### History table
- should have same cols as data table
- should have an extra PK col as primary key
- notes col

use utf8mb4 encoding for database

comment object:
- column_format: hidden, date


Databases:
_EL - stores the live data
_EL_history - stores the history counterpart to a table that exists in _EL.  table names will be identical to those in _EL except that it will have an additional UID column (if original table doesn't already have a unique key) as well as a 'User' field
