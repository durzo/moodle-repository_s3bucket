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
 * This plugin is used to access s3bucket files
 *
 * @package    repository_s3bucket
 * @copyright  2015 Renaat Debleu (www.eWallah.net) (based on work by Dongsheng Cai)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/repository/s3/S3.php');
require_once($CFG->dirroot . '/repository/s3bucket/manage_form.php');

require_login();
if (isguestuser()) {
    print_error('noguest');
}

$itemid = required_param('itemid', PARAM_INT);
$maxbytes = optional_param('maxbytes', 0, PARAM_INT);
$contextid = optional_param('ctx_id', SYSCONTEXTID, PARAM_INT);

$title = get_string('manageareafiles', 'repository_areafilesplus');

$PAGE->set_url('/repository/s3bucket/manage.php');
$PAGE->set_context(context::instance_by_id($contextid));
require_capability('repository/areafilesplus:manage', $PAGE->context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('popup');

$options = ['subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => -1, 'context' => $PAGE->context];

$usercontext = context_user::instance($USER->id);
$fs = get_file_storage();
$files = $fs->get_directory_files($usercontext->id, 'user', 'draft', $itemid, '/', false, false);
$filenames = [];
foreach ($files as $file) {
    $filenames[] = $file->get_filename();
}

$mform = new repository_s3bucket_manage_form(null, ['options' => $options, 'draftitemid' => $itemid, 'files' => $filenames]);

if ($data = $mform->get_data()) {
    if (!empty($data->deletefile)) {
        foreach (array_keys($data->deletefile) as $filename) {
            if ($file = $fs->get_file($usercontext->id, 'user', 'draft', $itemid, '/', $filename)) {
                $file->delete();
            }
        }
        $filenames = array_diff($filenames, array_keys($data->deletefile));
        $mform = new repository_s3bucket_manage_form(null, ['options' => $options, 'draftitemid' => $itemid, 'files' => $filenames]);
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
