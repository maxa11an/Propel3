<?php

/*
 *	$Id: VersionableBehaviorTest.php 1460 2010-01-17 22:36:48Z francois $
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../../../../generator/lib/util/PropelQuickBuilder.php';
require_once dirname(__FILE__) . '/../../../../../generator/lib/behavior/versionable/VersionableBehavior.php';
require_once dirname(__FILE__) . '/../../../../../runtime/lib/Propel.php';

/**
 * Tests for VersionableBehavior class
 *
 * @author     François Zaninotto
 * @version    $Revision$
 * @package    generator.behavior.versionable
 */
class VersionableBehaviorTest extends PHPUnit_Framework_TestCase
{
	public function basicSchemaDataProvider()
	{
		$schema = <<<EOF
<database name="versionable_behavior_test_1">
	<table name="versionable_behavior_test_1">
		<column name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
		<column name="bar" type="INTEGER" />
		<behavior name="versionable" />
	</table>
</database>
EOF;
		return array(array($schema));
	}
	
	/**
	 * @dataProvider basicSchemaDataProvider
	 */
	public function testModifyTableAddsVersionColumn($schema)
	{
		$builder = new PropelQuickBuilder();
		$builder->setSchema($schema);
		$expected = <<<EOF
-----------------------------------------------------------------------
-- versionable_behavior_test_1
-----------------------------------------------------------------------

DROP TABLE [versionable_behavior_test_1];

CREATE TABLE [versionable_behavior_test_1]
(
	[id] INTEGER NOT NULL PRIMARY KEY,
	[bar] INTEGER,
	[version] INTEGER DEFAULT 0
);
EOF;
		$this->assertContains($expected, $builder->getSQL());
	}

	public function testModifyTableAddsVersionColumnCustomName()
	{
			$schema = <<<EOF
<database name="versionable_behavior_test_1">
	<table name="versionable_behavior_test_1">
		<column name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
		<column name="bar" type="INTEGER" />
		<behavior name="versionable">
			<parameter name="version_column" value="foo_ver" />
		</behavior>
	</table>
</database>
EOF;
		$builder = new PropelQuickBuilder();
		$builder->setSchema($schema);
		$expected = <<<EOF
-----------------------------------------------------------------------
-- versionable_behavior_test_1
-----------------------------------------------------------------------

DROP TABLE [versionable_behavior_test_1];

CREATE TABLE [versionable_behavior_test_1]
(
	[id] INTEGER NOT NULL PRIMARY KEY,
	[bar] INTEGER,
	[foo_ver] INTEGER DEFAULT 0
);
EOF;
		$this->assertContains($expected, $builder->getSQL());
	}

	public function testModifyTableDoesNotAddVersionColumnIfExists()
	{
			$schema = <<<EOF
<database name="versionable_behavior_test_1">
	<table name="versionable_behavior_test_1">
		<column name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
		<column name="bar" type="INTEGER" />
		<column name="version" type="BIGINT" />
	</table>
</database>
EOF;
		$builder = new PropelQuickBuilder();
		$builder->setSchema($schema);
		$expected = <<<EOF
-----------------------------------------------------------------------
-- versionable_behavior_test_1
-----------------------------------------------------------------------

DROP TABLE [versionable_behavior_test_1];

CREATE TABLE [versionable_behavior_test_1]
(
	[id] INTEGER NOT NULL PRIMARY KEY,
	[bar] INTEGER,
	[version] BIGINT
);
EOF;
		$this->assertContains($expected, $builder->getSQL());
	}

	/**
	 * @dataProvider basicSchemaDataProvider
	 */
	public function testModifyTableAddsVersionTable($schema)
	{
		$builder = new PropelQuickBuilder();
		$builder->setSchema($schema);
		$expected = <<<EOF
-----------------------------------------------------------------------
-- versionable_behavior_test_1_version
-----------------------------------------------------------------------

DROP TABLE [versionable_behavior_test_1_version];

CREATE TABLE [versionable_behavior_test_1_version]
(
	[id] INTEGER NOT NULL,
	[bar] INTEGER,
	[version] INTEGER DEFAULT 0,
	PRIMARY KEY ([id],[version])
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([id]) REFERENCES versionable_behavior_test_1 ([id])
EOF;
		$this->assertContains($expected, $builder->getSQL());
	}

	public function testModifyTableAddsVersionTableCustomName()
	{
		$schema = <<<EOF
<database name="versionable_behavior_test_1">
	<table name="versionable_behavior_test_1">
		<column name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
		<column name="bar" type="INTEGER" />
		<behavior name="versionable">
		  <parameter name="version_table" value="foo_ver" />
		</behavior>
	</table>
</database>
EOF;
		$builder = new PropelQuickBuilder();
		$builder->setSchema($schema);
		$expected = <<<EOF
-----------------------------------------------------------------------
-- foo_ver
-----------------------------------------------------------------------

DROP TABLE [foo_ver];

CREATE TABLE [foo_ver]
(
	[id] INTEGER NOT NULL,
	[bar] INTEGER,
	[version] INTEGER DEFAULT 0,
	PRIMARY KEY ([id],[version])
);

-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY ([id]) REFERENCES versionable_behavior_test_1 ([id])
EOF;
		$this->assertContains($expected, $builder->getSQL());
	}

	public function testModifyTableDoesNotAddVersionTableIfExists()
	{
		$schema = <<<EOF
<database name="versionable_behavior_test_1">
	<table name="versionable_behavior_test_1">
		<column name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
		<column name="bar" type="INTEGER" />
		<behavior name="versionable" />
	</table>
	<table name="versionable_behavior_test_1_version">
		<column name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
		<column name="baz" type="INTEGER" />
	</table>
</database>
EOF;
		$builder = new PropelQuickBuilder();
		$builder->setSchema($schema);
		$expected = <<<EOF

-----------------------------------------------------------------------
-- versionable_behavior_test_1
-----------------------------------------------------------------------

DROP TABLE [versionable_behavior_test_1];

CREATE TABLE [versionable_behavior_test_1]
(
	[id] INTEGER NOT NULL PRIMARY KEY,
	[bar] INTEGER,
	[version] INTEGER DEFAULT 0
);

-----------------------------------------------------------------------
-- versionable_behavior_test_1_version
-----------------------------------------------------------------------

DROP TABLE [versionable_behavior_test_1_version];

CREATE TABLE [versionable_behavior_test_1_version]
(
	[id] INTEGER NOT NULL PRIMARY KEY,
	[baz] INTEGER
);

EOF;
		$this->assertEquals($expected, $builder->getSQL());
	}
}