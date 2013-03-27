<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $options = array('1'=>get_string('january', 'block_lsbu_course_overview'),
    				'2'=>get_string('february', 'block_lsbu_course_overview'),
    				'3'=>get_string('march', 'block_lsbu_course_overview'),
    				'4'=>get_string('april', 'block_lsbu_course_overview'),
    				'5'=>get_string('may', 'block_lsbu_course_overview'),
    				'6'=>get_string('june', 'block_lsbu_course_overview'),
    				'7'=>get_string('july', 'block_lsbu_course_overview'),
    				'8'=>get_string('august', 'block_lsbu_course_overview'),
    				'9'=>get_string('september', 'block_lsbu_course_overview'),
    				'10'=>get_string('october', 'block_lsbu_course_overview'),
    				'11'=>get_string('november', 'block_lsbu_course_overview'),
    				'12'=>get_string('december', 'block_lsbu_course_overview'));

    $settings->add(new admin_setting_configselect('block_lsbu_course_overview/academicyearstart', get_string('academicyearstart', 'block_lsbu_course_overview'),
                       get_string('configacademicyearstart', 'block_lsbu_course_overview'), 'all', $options));
}
