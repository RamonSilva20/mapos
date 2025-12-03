<?php

// log info
function log_info($task)
{
    $ci = &get_instance();
    $ci->load->model('Audit_model');

    $data = [
        'usuario' => $ci->session->userdata('nome_admin'),
        'ip' => $ci->input->ip_address(),
        'tarefa' => $task,
        'data' => date('Y-m-d'),
        'hora' => date('H:i:s'),
    ];

    $ci->Audit_model->add($data);
}
