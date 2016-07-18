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
