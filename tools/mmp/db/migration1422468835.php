<?php

class Migration1422468835 extends AbstractMigration
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
      "ALTER TABLE `albums` CHANGE  `name` `name` varchar(255) NOT NULL DEFAULT ''",
    );
  }

  protected function buildDown()
  {
    return array(
      "ALTER TABLE `albums` CHANGE  `name` `name` varchar(128) NOT NULL DEFAULT ''",
    );
  }

  protected function getRev() { return 1422468835; }

}
