<?php

class block_listado_cursos_usuario_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_elementos_por_pagina', get_string('elementosporpagina', 'block_listado_cursos_usuario'));
        $mform->setDefault('config_elementos_por_pagina', 4); // Valor por defecto
        $mform->setType('config_elementos_por_pagina', PARAM_INT);
    }
}
