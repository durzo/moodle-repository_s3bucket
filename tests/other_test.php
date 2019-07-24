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
require_once($CFG->dirroot . '/repository/s3bucket/lib.php');
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
     * Test class in system context.
     */
    public function test_class() {
        $this->resetAfterTest(true);
        $repo = new \repository_s3bucket(1);
        $repo->set_option(['endpoint' => 's3.eu-central-1.amazonaws.com', 'secret_key' => 'secret', 'bucket_name' => 'test',
                           'storageclass' => 'STANDARD', 'access_key' => 'abc']);
        $this->assertEquals('Amazon S3 bucket', $repo->get_name());
        $this->assertTrue($repo->check_login());
        $this->assertFalse($repo->contains_private_data());
        $this->assertCount(5, $repo->get_instance_option_names());
        $this->assertEquals('Amazon S3: test/filename.txt', $repo->get_file_source_info('filename.txt'));
        $this->assertFalse($repo->global_search());
        $this->assertEquals(2, $repo->supported_returntypes());
        $this->SetAdminUser();
        $this->assertEquals(2, $repo->check_capability());
        $this->expectException('Aws\S3\Exception\S3Exception');
        $repo->get_listing();
    }

    /**
     * Test empty in course context.
     */
    public function test_empty() {
        $this->resetAfterTest(true);
        $this->SetAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $repo = new \repository_s3bucket(2, $context);
        $this->expectException('moodle_exception');
        $repo->get_listing();
    }

    /**
     * Test get file in user context.
     */
    public function test_getfile() {
        global $USER;
        $this->resetAfterTest(true);
        $this->SetAdminUser();
        $context = context_user::instance($USER->id);
        $repo = new \repository_s3bucket($USER->id, $context);
        $repo->set_option(['endpoint' => 's3.eu-central-1.amazonaws.com', 'secret_key' => 'secret', 'bucket_name' => 'test',
                           'storageclass' => 'STANDARD', 'access_key' => 'abc']);
        $draft = file_get_unused_draft_itemid();
        $filerecord = ['component' => 'user', 'filearea' => 'draft', 'contextid' => $context->id,
                       'itemid' => $draft, 'filename' => 'filename.txt', 'filepath' => '/'];
        get_file_storage()->create_file_from_string($filerecord, 'test content');
        $this->expectException('Aws\S3\Exception\S3Exception');
        $repo->get_file('/filename.txt');
    }

    /**
     * Test instance form.
     */
    public function test_instance_form() {
        global $USER;
        $this->resetAfterTest(true);
        $this->SetAdminUser();
        $context = context_user::instance($USER->id);
        $para = ['plugin' => '$s3bucket', 'typeid' => '', 'instance' => null, 'contextid' => $context->id];
        $mform = new repository_instance_form('', $para);
        $data = ['endpoint' => 's3.amazonaws.com', 'secret_key' => 'secret', 'bucket_name' => 'test',
                 'storageclass' => 'STANDARD', 'access_key' => 'abc', 'attachments' => null];
        $this->assertEquals([], repository_s3bucket::instance_form_validation($mform, $data, []));
        ob_start();
        $mform->display();
        $out = ob_get_clean();
        $this->assertContains('There are required fields in this form marked', $out);
    }

    /**
     * Test form.
     * @coversDefaultClass repository_s3bucket/manage_form
     */
    public function test_form() {
        global $USER;
        $this->resetAfterTest(true);
        $this->SetAdminUser();
        $context = context_user::instance($USER->id);
        $page = new moodle_page();
        $page->set_context($context);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->set_url('/repository/s3bucket/manage.php');
        $form = new repository_s3bucket_testform();
        $mform = $form->getform();
        repository_s3bucket::instance_config_form($mform);
        ob_start();
        $mform->display();
        $out = ob_get_clean();
        $this->assertContains('There are required fields', $out);
        $data = ['endpoint' => 's3.eu-central-1.amazonaws.com', 'secret_key' => 'secret', 'bucket_name' => 'test',
                 'storageclass' => 'STANDARD', 'access_key' => 'abc', 'attachments' => null];
        $this->assertEquals([], repository_s3bucket::instance_form_validation($mform, $data, []));
        ob_start();
        $mform->display();
        $out = ob_get_clean();
        $this->assertContains('value="s3.amazonaws.com" selected', $out);
        $this->assertContains('value="STANDARD" selected', $out);
        $data['attachments'] = $form->draftid();
        $this->expectException('Aws\S3\Exception\S3Exception');
        repository_s3bucket::instance_form_validation($mform, $data, []);
    }
}

/**
 * Test form.
 *
 * @package    repository_s3bucket
 * @copyright  2018 iplusacademy.org
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass repository_s3bucket/manage_form
 */
class repository_s3bucket_testform extends repository_s3bucket_manage_form {

    /** @var stdClass Instance. */
    private $draft;
    /**
     * Form definition.
     */
    public function definition() {
        global $USER;
        $context = context_user::instance($USER->id);
        $this->accesskey = 'ABC';
        $fs = get_file_storage();
        $draft = file_get_unused_draft_itemid();
        $filerecord = ['component' => 'user', 'filearea' => 'draft', 'contextid' => $context->id,
                       'itemid' => $draft, 'filename' => 'filename.jpg', 'filepath' => '/'];
        $fs->create_file_from_string($filerecord, 'test content');
        $files = $fs->get_directory_files($context->id, 'user', 'draft', $draft, '/', false, false);
        $this->_customdata['draftitemid'] = $draft;
        $this->_customdata['options'] = ['subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => -1, 'context' => $context];
        $this->_customdata['files'] = $files;
        $this->draft = $draft;
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