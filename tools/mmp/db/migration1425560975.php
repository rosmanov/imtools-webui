<?php

class Migration1425560975 extends AbstractMigration
{
  /**
   * @todo Return action which should run before db modification
   */
  protected function buildPreup() { return array(); }
  /**
   * @todo Return action which should run after db modification
   */
  protected function buildPostup() { return array(); }
  /**
   * @todo Return action which should run before db rollback
   */
  protected function buildPredown() { return array(); }
  /**
   * @todo Return action which should run after db rollback
   */
  protected function buildPostdown() { return array(); }

  protected function buildUp()
  {
    return array(
      "ALTER TABLE `albums` ADD `modified` timestamp NOT NULL DEFAULT 'CURRENT_TIMESTAMP'",
      "ALTER TABLE `albums` ADD `interpolation` enum('nearest','linear','area','cubic','lanczos4') DEFAULT 'area'",
      "ALTER TABLE `images` ADD `width` int(10) unsigned NOT NULL DEFAULT '0'",
      "ALTER TABLE `images` ADD `height` int(10) unsigned NOT NULL DEFAULT '0'",
    );
  }

  protected function buildDown()
  {
    return array(
      "ALTER TABLE `albums` DROP `modified`",
      "ALTER TABLE `albums` DROP `interpolation`",
      "ALTER TABLE `images` DROP `width`",
      "ALTER TABLE `images` DROP `height`",
    );
  }

  protected function getRev() { return 1425560975; }

}
