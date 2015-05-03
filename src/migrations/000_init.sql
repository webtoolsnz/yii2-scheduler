DROP TABLE IF EXISTS `scheduler_log` ;
DROP TABLE IF EXISTS `scheduler_task` ;

-- -----------------------------------------------------
-- Table `scheduler_task`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `scheduler_task` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `schedule` VARCHAR(45) NOT NULL,
  `description` TEXT NOT NULL,
  `status_id` INT NOT NULL,
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `last_run` TIMESTAMP NULL DEFAULT NULL,
  `next_run` TIMESTAMP NULL DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `scheduler_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `scheduler_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `scheduled_task_id` INT(11) NOT NULL,
  `started_at` TIMESTAMP NOT NULL,
  `ended_at` TIMESTAMP NOT NULL,
  `output` TEXT NOT NULL,
  `error` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `fk_table1_scheduled_task_idx` (`scheduled_task_id` ASC),
  CONSTRAINT `fk_table1_scheduled_task`
    FOREIGN KEY (`scheduled_task_id`)
    REFERENCES `scheduler_task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;
