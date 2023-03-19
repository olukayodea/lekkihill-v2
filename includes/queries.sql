ALTER TABLE `wp_lekkihill_appointments` ADD `create_by` INT NOT NULL AFTER `patient_id`;
ALTER TABLE `wp_lekkihill_appointments` CHANGE `next_appointment` `next_appointment` DATETIME NULL;
ALTER TABLE `wp_lekkihill_appointments` CHANGE `message` `message` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `wp_lekkihill_inventory_category` ADD UNIQUE(`title`);
ALTER TABLE `wp_lekkihill_invoice_log` CHANGE `amount` `amount` DOUBLE NOT NULL;
ALTER TABLE `wp_lekkihill_clinic_medication` DROP `report_date`, DROP `report_time`;
ALTER TABLE `wp_lekkihill_clinic_medication` ADD `doctors_report_id` INT NOT NULL AFTER `patient_id`;
ALTER TABLE `wp_lekkihill_clinic_medication` ADD `invoice_id` INT NOT NULL AFTER `doctors_report_id`, ADD `quantity` INT NOT NULL AFTER `invoice_id`;
DROP TABLE `wp_lekkihill_patient_medication`;
ALTER TABLE `wp_lekkihill_clinic_fluid_balance` DROP `report_date`, DROP `report_time`;
ALTER TABLE `wp_lekkihill_clinic_medication` ADD `inventory_id` INT NOT NULL AFTER `medication`;
ALTER TABLE `wp_lekkihill_clinic_medication` ADD `sales_status` INT NOT NULL AFTER `frequency`;
ALTER TABLE `wp_lekkihill_clinic_doctors_report` DROP `recommended`;

CREATE TABLE `wp_lekkihill_clinic_lab` ( `ref` INT NOT NULL AUTO_INCREMENT , `patient_id` INT NOT NULL , `doctors_report_id` INT NOT NULL , `invoice_id` INT NOT NULL , `category_id` INT NOT NULL , `notes` TEXT NOT NULL , `status` VARCHAR(50) NOT NULL , `added_by` INT NOT NULL , `tech_id` INT NOT NULL , `create_time` DATETIME NOT NULL , `modify_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`ref`), INDEX (`patient_id`)) ENGINE = InnoDB;

ALTER TABLE `wp_lekkihill_clinic_lab` CHANGE `status` `status` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NEW';
ALTER TABLE `wp_lekkihill_clinic_lab` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `wp_lekkihill_clinic_lab` CHANGE `create_time` `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `wp_lekkihill_clinic_lab` ADD `result_time` DATETIME NULL AFTER `modify_time`;
