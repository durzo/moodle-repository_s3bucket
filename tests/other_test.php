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
 * Other tests.
 *
 * @package    repository_s3bucket
 * @copyright  2018 iplusacademy.org
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir. '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/repository/s3bucket/manage_form.php');

/**
 * Other tests.
 *
 * @package    repository_s3bucket
 * @copyright  2018 iplusacademy.org
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass repository_s3bucket
 */
class repository_s3bucket_other_tests extends \core_privacy\tests\provider_testcase {

    /**
     * Test privacy.
     * @covers repository_s3bucket\privacy\provider
     */
    public function test_privacy() {
        $privacy = new repository_s3bucket\privacy\provider();
        $this->assertEquals('privacy:metadata', $privacy->get_reason());
    }

    /**
     * Test class.
     */
    public function test_class() {
        $this->resetAfterTest(true);
        $this->SetAdminUser();
        $repo = new \repository_s3bucket(2);
        $this->assertEquals('Amazon S3 bucket', $repo->get_name());
        $this->assertTrue($repo->check_login());
        $this->assertFalse($repo->contains_private_data());
        $this->assertCount(5, $repo->get_instance_option_names());
        $this->assertEquals('Amazon S3: /bucket', $repo->get_file_source_info('bucket'));
        $this->assertFalse($repo->global_search());
        $this->assertEquals(2, $repo->supported_returntypes());
        $this->assertEquals(2, $repo->check_capability());
        try {
            $repo->get_listing();
        } catch (moodle_exception $e) {
            $this->assertEquals('Access key must be provided', $e->getMessage());
        }
    }

    /**
     * Test form.
     */
    public function test_form() {
        $this->resetAfterTest(true);
        $this->SetAdminUser();
        $context = context_system::instance();
        $page = new moodle_page();
        $page->set_context($context);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->set_url('/repository/s3bucket/manage.php');
        $form = new repository_s3bucket_testform();
        $mform = $form->getform();
        $out = repository_s3bucket::instance_config_form($mform);
        $this->assertEquals(null, $out);
        $data = ['endpoint' => 's3.amazonaws.com', 'secret_key' => 'secret',
                 'access_key' => 'abc', 'attachments' => $form->draftid()];
        $out = repository_s3bucket::instance_form_validation($mform, $data, [1 => '2']);
        $data = ['endpoint' => 's3.eu-central-1.amazonaws.com', 'secret_key' => 'secret',
                 'access_key' => 'abc', 'attachments' => $form->draftid()];
        $out = repository_s3bucket::instance_form_validation($mform, $data, [1 => '2']);
        $this->assertEquals([1 => '2'], $out);
        $para = ['plugin' => '$s3bucket', 'typeid' => '', 'instance' => null, 'contextid' => $context->id];
        $mform = new repository_instance_form('', $para);
        $out = repository_s3bucket::instance_form_validation($mform, $data, [1 => '2']);
        $this->assertEquals([1 => '2'], $out);
    }
}

/**
 * Test form.
 *
 * @package    repository_s3bucket
 * @copyright  2018 iplusacademy.org
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_s3bucket_testform extends repository_s3bucket_manage_form {

    /** @var stdClass Instance. */
    private $draft;
    /**
     * Form definition.
     */
    public function definition() {
        $this->accesskey = 'ABC';
        $context = context_system::instance();
        $fs = get_file_storage();
        $this->draft = file_get_unused_draft_itemid();
        $filerecord = ['component' => 'system', 'filearea' => 'draft', 'contextid' => $context->id,
                       'itemid' => $this->draft, 'filename' => 'filename.jpg', 'filepath' => '/'];
        $fs->create_file_from_string($filerecord, 'test content');
        $files = $fs->get_directory_files($context->id, 'sytem', 'draft', $this->draft, '/', false, false);
        $this->_customdata['draftitemid'] = $this->draft;
        $this->_customdata['options'] = ['subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => -1, 'context' => $context];
        $this->_customdata['files'] = $files;
    }
    /**
     * Returns form reference
     * @return MoodleQuickForm
     */
    public function getform() {
        $mform = $this->_form;
        $mform->_flagSubmitted = true;
        return $mform;
    }

    /**
     * Returns draftitemid
     * @return int draft item
     */
    public function draftid() {
        return $this->draft;
    }
}