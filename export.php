<?php
require_once('../../config.php');
require_once($CFG->libdir . '/tcpdf/tcpdf.php');
require_once($CFG->libdir . '/odslib.class.php');
require_once($CFG->libdir . '/excellib.class.php');

require_login();

$format = required_param('format', PARAM_ALPHA);
$courseid = optional_param('courseid', 0, PARAM_INT); // parametros


$context = context_system::instance();
if (!has_capability('block/listado_cursos_usuario:view', $context)) {
    $this->content = new stdClass;
    $this->content->text = 'No tienes permiso para ver este contenido.';
    $this->content->footer = '';
    return $this->content;
}


global $DB;

$sqlwhere = '';
$params = array();
if ($courseid > 0) {
    $sqlwhere = " AND c.id = :courseid ";
    $params['courseid'] = $courseid; //parámetros enviados.
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

$users = $DB->get_records_sql($sql, $params);

$namefile = "matricula_" . date('Y_m_d') . "";

switch ($format) {
    case 'excel':
        // Lógica para exportar a Excel
        $workbook = new \MoodleExcelWorkbook("-");
        $workbook->send($namefile);
        $worksheet = $workbook->add_worksheet('Usuarios');

        $col = 0;
        $worksheet->write_string(0, $col++, 'Usuario');
        $worksheet->write_string(0, $col++, 'Nombre');
        $worksheet->write_string(0, $col++, 'Apellido');
        $worksheet->write_string(0, $col++, 'Cursos');

        $row = 1;
        foreach ($users as $user) {
            $col = 0;
            $worksheet->write_string($row, $col++, $user->username);
            $worksheet->write_string($row, $col++, $user->firstname);
            $worksheet->write_string($row, $col++, $user->lastname);
            $worksheet->write_string($row, $col++, $user->coursename);
            $row++;
        }

        $workbook->close();

        break;
    case 'csv':
        // Lógica para exportar a CSV

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $namefile . '.csv"');

        $output = fopen('php://output', 'w');


        fputcsv($output, ['Usuario', 'Nombre', 'Apellido', 'Cursos']);

        foreach ($users as $user) {
            fputcsv($output, [
                mb_convert_encoding($user->username, 'ISO-8859-1', 'UTF-8'),
                mb_convert_encoding($user->firstname, 'ISO-8859-1', 'UTF-8'),
                mb_convert_encoding($user->lastname, 'ISO-8859-1', 'UTF-8'),
                mb_convert_encoding($user->coursename, 'ISO-8859-1', 'UTF-8')
            ]);
        }

        fclose($output);
        exit;

        break;
    case 'ods':
        // Lógica para exportar a ODS

        $workbook = new \MoodleODSWorkbook("-");
        $workbook->send("$namefile.ods");

        $worksheet = $workbook->add_worksheet('Usuarios');

        $col = 0;
        $worksheet->write_string(0, $col++, 'Usuario');
        $worksheet->write_string(0, $col++, 'Nombre');
        $worksheet->write_string(0, $col++, 'Apellido');
        $worksheet->write_string(0, $col++, 'Cursos');

        $row = 1;
        foreach ($users as $user) {
            $col = 0;
            $worksheet->write_string($row, $col++, $user->username);
            $worksheet->write_string($row, $col++, $user->firstname);
            $worksheet->write_string($row, $col++, $user->lastname);
            $worksheet->write_string($row, $col++, $user->coursename);
            $row++;
        }

        $workbook->close();

        break;
    case 'json':
        // Lógica para exportar a JSON

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $namefile . '.json"');

        $usersData = [];

        foreach ($users as $user) {
            $usersData[] = [
                'username' => $user->username,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'coursename' => $user->coursename
            ];
        }

        $jsonData = json_encode($usersData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        echo $jsonData;

        exit;

        break;
    case 'html':
        // Lógica para exportar a HTML

        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $namefile . '.html"');

        $output = fopen('php://output', 'w');

        fwrite($output, "<html><body>");
        fwrite($output, "<table border='1'>");
        fwrite($output, "<tr><th>Usuario</th><th>Nombre</th><th>Apellido</th><th>Cursos</th></tr>");

        foreach ($users as $user) {
            fwrite($output, "<tr>");
            fwrite($output, "<td>" . htmlspecialchars($user->username) . "</td>");
            fwrite($output, "<td>" . htmlspecialchars($user->firstname) . "</td>");
            fwrite($output, "<td>" . htmlspecialchars($user->lastname) . "</td>");
            fwrite($output, "<td>" . htmlspecialchars($user->coursename) . "</td>");
            fwrite($output, "</tr>");
        }

        fwrite($output, "</table>");
        fwrite($output, "</body></html>");

        fclose($output);
        exit;

        break;

    case 'pdf':

        // Lógica para exportar a PDF

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Tu Nombre');
        $pdf->SetTitle('Exportación de Usuarios');
        $pdf->SetSubject('Reporte de Usuarios');

        $pdf->AddPage();

        $html = '<h1>Lista de Usuarios</h1>
        <table border="1" cellpadding="4">
        <tr>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Cursos</th>
        </tr>';

        foreach ($users as $user) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($user->username) . '</td>';
            $html .= '<td>' . htmlspecialchars($user->firstname) . '</td>';
            $html .= '<td>' . htmlspecialchars($user->lastname) . '</td>';
            $html .= '<td>' . htmlspecialchars($user->coursename) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('' . $namefile . '.pdf', 'D');
        exit;

        break;
    default:
        // Manejar error o formato no soportado
        print_error('exportformatnotsupported', 'block_listado_cursos_usuario', '', $format);
        exit;

        break;
}
