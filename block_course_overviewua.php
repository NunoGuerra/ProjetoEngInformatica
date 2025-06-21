<?php

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block course_overviewua definido aqui.
 *
 * @package     block_course_overviewuab
 * @copyright   2022 Nuno Guerra <npguerra10@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_overviewuab extends block_base {

    /**
     * Inicializacao das variaveis membro
     */
    public function init() {
        // Necessario para o Moodle diferenciar entre blocos
        $this->title = get_string('pluginname', 'block_course_overviewua');
    }

    /**
     * Retorna o conteudo do bloco.
     *
     * @return stdClass Conteudo do bloco.
     */
    public function get_content() {
        global $USER, $DB, $CFG, $OUTPUT, $PAGE;
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->text = '';
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            global $CFG;
            require_once $CFG->dirroot.'/lib/enrollib.php';
            // Chamada da classe personalizada do forum 
            $sitecontext = context_system::instance();
            $sitediscussions = new \block_course_overviewuab\course_overviewuab($sitecontext);
            $discussions = $sitediscussions->get_discussion_forum();
            $reply_count = $sitediscussions->get_discussion_count();
            $data = array();
            $i = 0;
            if(!empty($discussions)){
            foreach ($discussions as &$discussion) {
                //Salta se nao houver mensagens novas no forum 
                if(empty($reply_count) ||  !isset($reply_count[$discussion->discussion])){                        
                    continue;
                }
            
                $data['discussion'][$i]['coursename'] = $discussion->coursename;
                $data['discussion'][$i]['subject'] = $discussion->subject;
                $data['discussion'][$i]['courselink'] = $CFG->wwwroot.'/course/view.php?id='.$discussion->course;
                $data['discussion'][$i]['forumlink'] = $CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion;
                $data['discussion'][$i]['reply_count'] = $reply_count[$discussion->discussion];
                $data['discussion'][$i]['message'] = ($reply_count[$discussion->discussion] > 1) ? 'Novas Menssagens' : 'Nova Mensagem';
                $i++;
            }
            $url = $CFG->wwwroot.'/course/view.php?id=';
            $allcourses = [];
            if ($courses = enrol_get_my_courses()) {
                    $courses = array_values($courses);
                    $allcourses['courses'] = $courses;
                    $allcourses['title'] = get_string('mycourses');
                    $allcourses['curl'] = $url;
                    $this->content->text .= $OUTPUT->render_from_template('block_course_overviewuab/courses', $allcourses);
                }

                $this->content->text .= $OUTPUT->render_from_template('block_course_overviewuab/discussion', $data);
        }else{
            //Se nao existir dados entao mostra esta mensagem
          $this->content->text .= html_writer::div(get_string('nothingtodisplay', 'block_course_overviewuab'), 'alert alert-info mt-3');   
        }
        }

        return $this->content;
    }

    /**
     * Define a configuracao dos dados.
     *
     * Funcao chamada imediatamente depois de init().
     */
    public function specialization() {

        // Faz load ao titulo definido pelo utilizador e certifica-se que nunca esta vazio.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_course_overviewuab');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Permitir multiplas instancias num unico curso? 
     *
     * @return bool True se multiplas instancias permitidas, false caso contrario.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Define os applicable formats para o bloco.
     *
     * @return string[] Array de paginas e permissoes.
     */
    public function applicable_formats() {
        return array(
            'all' => true, 'site' => true, 'course' => true, 'my' => true
        );
    }

}
