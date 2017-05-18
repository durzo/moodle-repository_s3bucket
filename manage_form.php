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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. "/formslib.php");

/**
 * Form allowing to collect files from S3 bucket
 *
 * @package    repository_s3bucket
 * @copyright  2015 Renaat Debleu (www.eWallah.net) (based on work by Dongsheng Cai)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_s3bucket_manage_form extends moodleform {

    /**
     * Define this form - called from the parent constructor
     */
    public function definition() {
        $mform = $this->_form;

        $itemid  = $this->_customdata['draftitemid'];
        $options = $this->_customdata['options'];
        $files   = $this->_customdata['files'];

        $mform->addElement('hidden', 'itemid');
        $mform->addElement('hidden', 'maxbytes');
        $mform->addElement('hidden', 'ctx_id');

        if (count($files)) {
            foreach ($files as $file) {
                $mform->addElement('checkbox', 'deletefile['.$file.']', '', $file);
            }
            $mform->addElement('submit', 'delete', get_string('deleteselected', 'repository_areafilesplus'));
        } else {
            $mform->addElement('static', '', '', get_string('nofiles', 'repository_areafilesplus'));
        }

        $this->set_data(['itemid' => $itemid, 'maxbytes' => $options['maxbytes'], 'ctx_id' => $options['context']->id]);
    }
}
