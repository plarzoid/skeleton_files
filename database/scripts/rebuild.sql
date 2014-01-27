SELECT 'Building database...' as '';
SOURCE ../creation/model.sql

SELECT 'Adding Admin account...' as '';
SOURCE ../creation/add_admin.sql

SELECT 'Adding PHP account...' as '';
/*SOURCE ../creation/allow_access.sql*/
SOURCE ../creation/refresh_access.sql

