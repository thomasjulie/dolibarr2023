CREATE TABLE IF NOT EXISTS llx_prospectCustomerType
 (
    rowid INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    code VARCHAR(50) NOT NULL, 
    label VARCHAR(128) DEFAULT NULL,
    active TINYINT NOT NULL DEFAULT 1
) ENGINE = InnoDB;

ALTER TABLE `llx_prospectCustomerType` ADD UNIQUE(`code`);