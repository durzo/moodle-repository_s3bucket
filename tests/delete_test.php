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
 * Delete tests.
 *
 * @package    repository_s3bucket
 * @copyright  2017 Renaat Debleu (www.eWallah.net) (based on work by Dongsheng Cai)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/repository/s3bucket/lib.php');

/**
 * Delete tests.
 *
 * @package    repository_s3bucket
 * @copyright  2017 Renaat Debleu (www.eWallah.net) (based on work by Dongsheng Cai)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass repository_s3bucket
 */
class repository_s3bucket_delete_tests extends \advanced_testcase {

    /**
     * Test deleting a s3 bucket.
     */
    public function test_deletebucket() {
        global $DB, $USER;
        $this->resetAfterTest(true);
        $type = 's3bucket';
        $reference = 'filename.jpg';
        $this->assertEquals(7, $DB->count_records('repository_instances'));
        $this->getDataGenerator()->create_repository_type($type);
        $repoid = $this->getDataGenerator()->create_repository($type)->id;
        $this->SetAdminUser();
        $repo = new \repository_s3bucket($repoid);
        $this->assertEquals(8, $DB->count_records('repository_instances'));
        $fs = get_file_storage();
        $filerecord = ['component' => 'user', 'filearea' => 'draft', 'contextid' => context_user::instance($USER->id)->id,
                       'itemid' => file_get_unused_draft_itemid(), 'filename' => $reference, 'filepath' => '/'];
        $this->assertEquals(0, $DB->count_records('files_reference'));
        $fs->create_file_from_reference($filerecord, $repoid, $reference);
        $this->assertEquals(1, $DB->count_records('files_reference'));
        $repo->delete();
        $this->assertEquals(0, $DB->count_records('files_reference'));
    }
}