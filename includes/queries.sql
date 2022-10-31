ALTER TABLE `wp_lekkihill_appointments` ADD `create_by` INT NOT NULL AFTER `patient_id`;
ALTER TABLE `wp_lekkihill_appointments` CHANGE `next_appointment` `next_appointment` DATETIME NULL;
ALTER TABLE `wp_lekkihill_appointments` CHANGE `message` `message` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
