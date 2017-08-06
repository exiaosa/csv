"# csv" 

**NOTES**:  
I added the command option -d for MySQL database name which is necessary for connect to database.  

**Command option guidance:**  
**[Options]**  
--file [csv file name] – this is the name of the CSV to be parsed  
--create_table – this will cause the MySQL users table to be built (and no further action will be taken)  
--dry_run – this will be used with the --file directive in the instance that we want to run thescript but not insert into the DB. All other functions will be executed, but the database won'tbe altered.
-u – MySQL username  
-p – MySQL password  
-h – MySQL host  
-d – MySQL database name  
--help – which will output the above list of directives with details.  

**[Usage]**  
run the script: 
--file[csv file name] --dry_run  

run the script with inserting data into database:  
--file[csv file name] --create_table -h hostname -u username -p password -d database

