<?php

class block_listado_cursos_usuario extends block_base
{
    public function init()
    {
        $this->title = get_string('pluginname', 'block_listado_cursos_usuario');
    }

    public function instance_config_save($data, $nolongerused = false)
    {
        if (empty($data->elementos_por_pagina)) {
            $data->elementos_por_pagina = 4; // Valor por defecto
        }

        return parent::instance_config_save($data, $nolongerused);
    }

    public function has_config()
    {
        return true;
    }

    public function get_content()
    {
        global $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }



        //verifico los permisos
        $context = context_system::instance();
        if (!has_capability('block/listado_cursos_usuario:view', $context)) {
            $this->content = new stdClass;
            $this->content->text   = 'No tienes permiso para ver este contenido.';
            $this->content->footer = '';
            return $this->content;
        }

        $this->content = new stdClass;


        //lista de opciones a exportar
        $formats = [
            'excel' => 'Excel',
            'csv' => 'CSV',
            'ods' => 'ODS',
            'json' => 'JSON',
            'html' => 'HTML',
            'pdf' => 'PDF'
        ];


        $page = optional_param('page', 0, PARAM_INT); // Número de página actual


        // Obtener el curso seleccionado
        $selectedcourseid = optional_param('courseid', 0, PARAM_INT);

        // Visibles y el numero uno es conocido como curso de sitio
        $courses = $DB->get_records_select('course', "id != 1 AND visible = 1");

        $courseoptions = [];
        $courseoptions[0] = get_string('name_all_course', 'block_listado_cursos_usuario'); // Opción para no filtrar por curso, todos
        foreach ($courses as $course) {

            $courseoptions[$course->id] = format_string($course->fullname);
        }


        // Número de registros por página, cambio desde configuracion
        $perpage = !empty($this->config->elementos_por_pagina) ? $this->config->elementos_por_pagina : 4;

        $sqlwhere = '';
        if ($selectedcourseid > 0) {
            $sqlwhere = " AND c.id = :courseid ";
            $params = array('courseid' => $selectedcourseid);
        } else {
            $params = array();
        }

        $sql = "SELECT u.id, u.username, u.firstname, u.lastname, 
        GROUP_CONCAT(c.fullname SEPARATOR ', ') AS coursename
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        JOIN {course} c ON c.id = e.courseid
        WHERE 1=1 $sqlwhere
        GROUP BY u.id, u.username, u.firstname, u.lastname
        ORDER BY u.id";

        $params = ($selectedcourseid > 0) ? array('courseid' => $selectedcourseid) : array();


        // Calcular el total de registros para la paginación
        $totalcount = $DB->count_records_sql("SELECT COUNT(*) FROM ($sql) temp", $params);

        // Obtener los registros de la página actual
        $start = $page * $perpage;
        $users = $DB->get_records_sql($sql, $params, $start, $perpage);
        $exporturl = new moodle_url('/blocks/listado_cursos_usuario/export.php');


        $selectedcourseid = optional_param('courseid', 0, PARAM_INT);

        //exportar seccion
        $this->content->text .= '
        ' . get_string('download_name', 'block_listado_cursos_usuario') . ' 
        <form action="' . $exporturl . '" method="get" id="export-form">
            <input type="hidden" name="courseid" value="' . $selectedcourseid . '">
            <label for="formatSelect" class="sr-only">' . get_string('select_format', 'block_listado_cursos_usuario') . '</label>
            <select name="format" id="formatSelect" class="btn btn-outline-secondary dropdown-toggle icon-no-margin">';

        foreach ($formats as $key => $value) {
            $this->content->text .= '<option value="' . $key . '">' . $value . '</option>';
        }

        $this->content->text .= '</select>
            <button type="submit" class="btn btn-primary float-sm-right float-right">' . get_string('export_name', 'block_listado_cursos_usuario') . '</button>
        </form>
        ';


        //filtro seccion
        $this->content->text .= '<form method="get" id="filtro-form">';
        $this->content->text .= '<select name="courseid" class="btn btn-outline-secondary dropdown-toggle icon-no-margin">';
        foreach ($courseoptions as $id => $name) {
            $selected = ($id == $selectedcourseid) ? 'selected' : '';
            $this->content->text .= "<option value=\"$id\" $selected>$name</option>";
        }
        $this->content->text .= '</select>';
        $this->content->text .= '<input type="submit" value="Filtrar"  class="btn btn-primary float-sm-right float-right">';
        $this->content->text .= '</form>';


        $contador = 1;
        $this->content->text .= '<table class="lista_cursos table">';
        $this->content->text .= '<tr><th>#</th>
        <th>' . get_string('courses_name', 'block_listado_cursos_usuario') . '</th>
        <th>' . get_string('name_firstname', 'block_listado_cursos_usuario') . '</th>
        <th>' . get_string('name_lastname', 'block_listado_cursos_usuario') . '</th>
        <th>' . get_string('name_course', 'block_listado_cursos_usuario') . '</th></tr>';
        foreach ($users as $user) {
            $this->content->text .= '<tr>';
            $this->content->text .= '<td>' . $contador . '</td>';
            $this->content->text .= '<td>' . htmlspecialchars($user->username) . '</td>';
            $this->content->text .= '<td>' . htmlspecialchars($user->firstname) . '</td>';
            $this->content->text .= '<td>' . htmlspecialchars($user->lastname) . '</td>';
            $this->content->text .= '<td>' . htmlspecialchars($user->coursename) . '</td>';
            $this->content->text .= '</tr>';
            $contador++;
        }
        $this->content->text .= '</table>';

        // Añadir la barra de paginación al final
        $this->content->text .= $OUTPUT->paging_bar($totalcount, $page, $perpage, $PAGE->url);

        $this->content->footer = '';


        return $this->content;
    }
}
