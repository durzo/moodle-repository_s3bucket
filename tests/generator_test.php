<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Amazon S3bucket repository data generator test
 *
 * @package    repository_s3bucket
 * @copyright  2017 Renaat Debleu (www.eWallah.net) (based on work by Dongsheng Cai)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Amazon S3bucket repository data generator test
 *
 * @package    repository_s3bucket
 * @copyright  2017 Renaat Debleu (www.eWallah.net) (based on work by Dongsheng Cai)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass repository_s3bucket
 */
class repository_s3bucket_generator_testcase extends advanced_testcase {

    /**
     * Basic test of creation of repository types.
     *
     * @return void
     */
    public function test_create_type() {
        global $DB;
        $this->resetAfterTest(true);
        $type = 's3bucket';
        $repotype = $this->getDataGenerator()->create_repository_type($type);
        $this->assertEquals($repotype->type, $type, 'Unexpected name after creating repository type ' . $type);
        $this->assertTrue($DB->record_exists('repository', ['type' => $type, 'visible' => 1]));

        $caughtexception = false;
        try {
            $this->getDataGenerator()->create_repository_type($type);
        } catch (repository_exception $e) {
            if ($e->getMessage() === 'This repository already exists') {
                $caughtexception = true;
            }
        }
        $this->assertTrue($caughtexception, "Repository type '$type' should have already been enabled");
    }

    /**
     * Installing repository tests
     *
     * @return void
     */
    public function test_install_repository() {
        $this->resetAfterTest(true);
        $repo = 's3bucket';
        $plugintype = new repository_type($repo);
        $pluginid = $plugintype->create(false);
        $this->assertInternalType('int', $pluginid);
    }
}
