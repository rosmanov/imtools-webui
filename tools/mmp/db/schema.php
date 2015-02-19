<?php
class Schema extends AbstractSchema
{
  protected function buildQueries()
  {
    return array(
      "DROP TABLE IF EXISTS `albums`",
      "CREATE TABLE `albums` (\n"
      . "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n"
      . "  `name` varchar(128) NOT NULL DEFAULT '',\n"
      . "  PRIMARY KEY (`id`)\n"
      . ") ENGINE=InnoDB DEFAULT CHARSET=utf8",
      "DROP TABLE IF EXISTS `db_revisions`",
      "CREATE TABLE `db_revisions` (\n"
      . "  `rev` bigint(20) unsigned NOT NULL DEFAULT '0',\n"
      . "  PRIMARY KEY (`rev`)\n"
      . ") ENGINE=MyISAM DEFAULT CHARSET=utf8",
      "INSERT INTO `db_revisions` SET rev=1422468275",
    );
  }
}
