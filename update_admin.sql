UPDATE users 
SET password = '$2y$10$r43kMAFY6BkANUDrpH0MMOIurOsowJfd6RTg6fQecrSnDVmc8611m' 
WHERE email = 'admin@warehouse.com';

SELECT id, name, email, role, SUBSTRING(password, 1, 20) as pwd_check 
FROM users 
WHERE email = 'admin@warehouse.com';
