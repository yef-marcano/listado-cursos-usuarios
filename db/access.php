<?php
// Este archivo es parte de Moodle - http://moodle.org/
//
// Moodle es un software libre; puedes redistribuirlo y/o modificarlo
// bajo los términos de la Licencia Pública General GNU publicada por
// la Free Software Foundation, ya sea la versión 3 de la Licencia, o
// (a tu elección) cualquier versión posterior.
//
// Moodle se distribuye con la esperanza de que sea útil,
// pero SIN NINGUNA GARANTÍA; incluso sin la garantía implícita de
// COMERCIALIZACIÓN o ADECUACIÓN PARA UN PROPÓSITO PARTICULAR. Consulta
// la Licencia Pública General GNU para más detalles.
//
// Deberías haber recibido una copia de la Licencia Pública General GNU
// junto con Moodle. Si no, consulta <http://www.gnu.org/licenses/>.

/**
 * Detalles de la versión del bloque listado_cursos_usuario
 *
 * @package    block_listado_cursos_usuario
 * @copyright  2024 YM (Ymrest)
 * @license    http://www.gnu.org/copyleft/gpl.html Licencia Pública General GNU v3 o posterior
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'block/listado_cursos_usuario:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),
);