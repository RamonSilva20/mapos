<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Os extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->model('os_model');
        $this->data['menuOs'] = 'OS';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        $this->load->library('pagination');
        $this->load->model('mapos_model');

        $where_array = [];

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');
        $inputDe = $this->input->get('data');
        $inputAte = $this->input->get('data2');

        if ($pesquisa) {
            $where_array['pesquisa'] = $pesquisa;
        }
        if ($status) {
            $where_array['status'] = $status;
        }
        if ($inputDe) {
            $de = explode('/', $inputDe);
            $de = $de[2] . '-' . $de[1] . '-' . $de[0];

            $where_array['de'] = $de;
        }
        if ($inputAte) {
            $ate = explode('/', $inputAte);
            $ate = $ate[2] . '-' . $ate[1] . '-' . $ate[0];

            $where_array['ate'] = $ate;
        }

        $this->data['configuration']['base_url'] = site_url('os/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->os_model->count('os');
        if(count($where_array) > 0) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}&status={$status}&data={$inputDe}&data2={$inputAte}";
            $this->data['configuration']['first_url'] = base_url("index.php/os/gerenciar")."\?pesquisa={$pesquisa}&status={$status}&data={$inputDe}&data2={$inputAte}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->os_model->getOs(
            'os',
            'os.*,
            COALESCE((SELECT SUM(produtos_os.preco * produtos_os.quantidade ) FROM produtos_os WHERE produtos_os.os_id = os.idOs), 0) totalProdutos,
            COALESCE((SELECT SUM(servicos_os.preco * servicos_os.quantidade ) FROM servicos_os WHERE servicos_os.os_id = os.idOs), 0) totalServicos',
            $where_array,
            $this->data['configuration']['per_page'],
            $this->uri->segment(3)
        );

        $this->data['texto_de_notificacao'] = $this->data['configuration']['notifica_whats'];
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['view'] = 'os/os';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar O.S.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('os') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $dataInicial = $this->input->post('dataInicial');
            $dataFinal = $this->input->post('dataFinal');
            $termoGarantiaId = $this->input->post('termoGarantia');

            try {
                $dataInicial = explode('/', $dataInicial);
                $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];

                if ($dataFinal) {
                    $dataFinal = explode('/', $dataFinal);
                    $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
                } else {
                    $dataFinal = date('Y/m/d');
                }

                $termoGarantiaId = (! $termoGarantiaId == null || ! $termoGarantiaId == '')
                    ? $this->input->post('garantias_id')
                    : null;
            } catch (Exception $e) {
                $dataInicial = date('Y/m/d');
                $dataFinal = date('Y/m/d');
            }

            $data = [
                'dataInicial' => $dataInicial,
                'clientes_id' => $this->input->post('clientes_id'), //set_value('idCliente'),
                'usuarios_id' => $this->input->post('usuarios_id'), //set_value('idUsuario'),
                'dataFinal' => $dataFinal,
                'garantia' => set_value('garantia'),
                'garantias_id' => $termoGarantiaId,
                'descricaoProduto' => $this->input->post('descricaoProduto'),
                'imprimir_descricao' => $this->input->post('imprimir_descricao') ? 1 : 0,
                'defeito' => $this->input->post('defeito'),
                'imprimir_defeito' => $this->input->post('imprimir_defeito') ? 1 : 0,
                'status' => set_value('status'),
                'observacoes' => $this->input->post('observacoes'),
                'imprimir_observacoes' => $this->input->post('imprimir_observacoes') ? 1 : 0,
                'laudoTecnico' => $this->input->post('laudoTecnico'),
                'imprimir_laudo' => $this->input->post('imprimir_laudo') ? 1 : 0,
                'faturado' => 0,
            ];

            if (is_numeric($id = $this->os_model->add('os', $data, true))) {
                $idOs = $id;
                
                // Salvar parcelas se houver
                $parcelasJson = $this->input->post('parcelas_json');
                if (!empty($parcelasJson)) {
                    $parcelas = json_decode($parcelasJson, true);
                    if (is_array($parcelas) && count($parcelas) > 0) {
                        $this->load->model('parcelas_os_model');
                        $this->parcelas_os_model->saveParcelas($idOs, $parcelas);
                    }
                }
                $status = set_value('status');
                
                // Se o status inicia garantia e tem garantia definida, definir data de início
                if ($this->statusIniciaGarantia($status) && !empty($data['garantia']) && $data['garantia'] > 0) {
                    $this->db->where('idOs', $idOs);
                    $this->db->update('os', ['dataInicioGarantia' => date('Y-m-d')]);
                    log_info("Garantia INICIADA no adicionar. OS: {$idOs}, Data início: " . date('Y-m-d') . ", Dias: {$data['garantia']}");
                }

                $this->load->model('mapos_model');
                $this->load->model('usuarios_model');

                $os = $this->os_model->getById($idOs);
                $emitente = $this->mapos_model->getEmitente();

                $tecnico = $this->usuarios_model->getById($os->usuarios_id);

                // Verificar configuração de notificação
                if ($this->data['configuration']['os_notification'] != 'nenhum' && $this->data['configuration']['email_automatico'] == 1) {
                    $remetentes = [];
                    switch ($this->data['configuration']['os_notification']) {
                        case 'todos':
                            array_push($remetentes, $os->email);
                            array_push($remetentes, $tecnico->email);
                            array_push($remetentes, $emitente->email);
                            break;
                        case 'cliente':
                            array_push($remetentes, $os->email);
                            break;
                        case 'tecnico':
                            array_push($remetentes, $tecnico->email);
                            break;
                        case 'emitente':
                            array_push($remetentes, $emitente->email);
                            break;
                        default:
                            array_push($remetentes, $os->email);
                            break;
                    }
                    $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço - Criada');
                }

                $this->session->set_flashdata('success', 'OS adicionada com sucesso, você pode adicionar produtos ou serviços a essa OS nas abas de Produtos e Serviços!');
                log_info('Adicionou uma OS. ID: ' . $id);
                redirect(site_url('os/editar/') . $id);
            } else {
                $this->data['custom_error'] = '<div class="alert">Ocorreu um erro.</div>';
            }
        }

        $this->data['view'] = 'os/adicionarOs';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->os_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'OS não encontrada ou parâmetro inválido.');
            redirect('os/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar O.S.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $this->data['texto_de_notificacao'] = $this->data['configuration']['notifica_whats'];

        $this->data['editavel'] = $this->os_model->isEditable($this->input->post('idOs'));
        if (! $this->data['editavel']) {
            $this->session->set_flashdata('error', 'Esta OS já e seu status não pode ser alterado e nem suas informações atualizadas. Por favor abrir uma nova OS.');

            redirect(site_url('os'));
        }

        if ($this->form_validation->run('os') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $dataInicial = $this->input->post('dataInicial');
            $dataFinal = $this->input->post('dataFinal');
            $termoGarantiaId = $this->input->post('garantias_id') ?: null;

            try {
                $dataInicial = explode('/', $dataInicial);
                $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];

                $dataFinal = explode('/', $dataFinal);
                $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
            } catch (Exception $e) {
                $dataInicial = date('Y/m/d');
            }

            $data = [
                'dataInicial' => $dataInicial,
                'dataFinal' => $dataFinal,
                'garantia' => $this->input->post('garantia'),
                'garantias_id' => $termoGarantiaId,
                'descricaoProduto' => $this->input->post('descricaoProduto'),
                'imprimir_descricao' => $this->input->post('imprimir_descricao') ? 1 : 0,
                'defeito' => $this->input->post('defeito'),
                'imprimir_defeito' => $this->input->post('imprimir_defeito') ? 1 : 0,
                'status' => $this->input->post('status'),
                'observacoes' => $this->input->post('observacoes'),
                'imprimir_observacoes' => $this->input->post('imprimir_observacoes') ? 1 : 0,
                'laudoTecnico' => $this->input->post('laudoTecnico'),
                'imprimir_laudo' => $this->input->post('imprimir_laudo') ? 1 : 0,
                'usuarios_id' => $this->input->post('usuarios_id'),
                'clientes_id' => $this->input->post('clientes_id'),
            ];
            $os = $this->os_model->getById($this->input->post('idOs'));
            $novoStatus = $this->input->post('status');
            $statusAntigo = $os->status;

            //Verifica para poder fazer a devolução do produto para o estoque caso OS seja cancelada.

            if (strtolower($this->input->post('status')) == 'cancelado' && strtolower($os->status) != 'cancelado') {
                $this->devolucaoEstoque($this->input->post('idOs'));
            }

            if (strtolower($os->status) == 'cancelado' && strtolower($this->input->post('status')) != 'cancelado') {
                $this->debitarEstoque($this->input->post('idOs'));
            }

            // Gerenciar data de início da garantia
            $statusAntigoIniciaGarantia = $this->statusIniciaGarantia($statusAntigo);
            $novoStatusIniciaGarantia = $this->statusIniciaGarantia($novoStatus);

            // Se mudou para um status que inicia garantia E ainda não tem data de início
            if ($novoStatusIniciaGarantia && !$statusAntigoIniciaGarantia) {
                if (empty($os->dataInicioGarantia) && !empty($data['garantia']) && $data['garantia'] > 0) {
                    $data['dataInicioGarantia'] = date('Y-m-d');
                    log_info("Garantia INICIADA no editar. OS: {$this->input->post('idOs')}, Data início: " . date('Y-m-d') . ", Dias: {$data['garantia']}");
                }
            }
            // Se mudou de um status que inicia garantia para um que não inicia (ex: Aprovado → Orçamento)
            elseif ($statusAntigoIniciaGarantia && !$novoStatusIniciaGarantia) {
                $data['dataInicioGarantia'] = null;
                log_info("Garantia CANCELADA (status voltou para Orçamento/Negociação). OS: {$this->input->post('idOs')}");
            }

            if ($this->os_model->edit('os', $data, 'idOs', $this->input->post('idOs')) == true) {
                // Salvar parcelas se houver
                $parcelasJson = $this->input->post('parcelas_json');
                if (!empty($parcelasJson)) {
                    $parcelas = json_decode($parcelasJson, true);
                    if (is_array($parcelas) && count($parcelas) > 0) {
                        $this->load->model('parcelas_os_model');
                        $this->parcelas_os_model->saveParcelas($this->input->post('idOs'), $parcelas);
                    }
                }
                
                $this->load->model('mapos_model');
                $this->load->model('usuarios_model');

                $idOs = $this->input->post('idOs');

                $os = $this->os_model->getById($idOs);
                $emitente = $this->mapos_model->getEmitente();
                $tecnico = $this->usuarios_model->getById($os->usuarios_id);

                // Verificar configuração de notificação
                if ($this->data['configuration']['os_notification'] != 'nenhum' && $this->data['configuration']['email_automatico'] == 1) {
                    $remetentes = [];
                    switch ($this->data['configuration']['os_notification']) {
                        case 'todos':
                            array_push($remetentes, $os->email);
                            array_push($remetentes, $tecnico->email);
                            array_push($remetentes, $emitente->email);
                            break;
                        case 'cliente':
                            array_push($remetentes, $os->email);
                            break;
                        case 'tecnico':
                            array_push($remetentes, $tecnico->email);
                            break;
                        case 'emitente':
                            array_push($remetentes, $emitente->email);
                            break;
                        default:
                            array_push($remetentes, $os->email);
                            break;
                    }
                    $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço - Editada');
                }

                $this->session->set_flashdata('success', 'Os editada com sucesso!');
                log_info('Alterou uma OS. ID: ' . $this->input->post('idOs'));
                redirect(site_url('os/editar/') . $this->input->post('idOs'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));

        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        
        // Carregar parcelas da OS
        $this->load->model('parcelas_os_model');
        $this->data['parcelas'] = $this->parcelas_os_model->getByOs($this->uri->segment(3));
        $this->data['anexos'] = $this->os_model->getAnexos($this->uri->segment(3));
        $this->data['anotacoes'] = $this->os_model->getAnotacoes($this->uri->segment(3));

        if ($return = $this->os_model->valorTotalOS($this->uri->segment(3))) {
            $this->data['totalServico'] = $return['totalServico'];
            $this->data['totalProdutos'] = $return['totalProdutos'];
        }

        $this->load->model('mapos_model');
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->data['view'] = 'os/editarOs';

        return $this->layout();
    }

    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->data['texto_de_notificacao'] = $this->data['configuration']['notifica_whats'];

        $this->load->model('mapos_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['anexos'] = $this->os_model->getAnexos($this->uri->segment(3));
        $this->data['anotacoes'] = $this->os_model->getAnotacoes($this->uri->segment(3));
        $this->data['editavel'] = $this->os_model->isEditable($this->uri->segment(3));
        $this->data['qrCode'] = $this->os_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        $this->data['modalGerarPagamento'] = $this->load->view(
            'cobrancas/modalGerarPagamento',
            [
                'id' => $this->uri->segment(3),
                'tipo' => 'os',
            ],
            true
        );
        $this->data['view'] = 'os/visualizarOs';
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);

        if ($return = $this->os_model->valorTotalOS($this->uri->segment(3))) {
            $this->data['totalServico'] = $return['totalServico'];
            $this->data['totalProdutos'] = $return['totalProdutos'];
        }

        return $this->layout();
    }

    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma1 += $cpf[$i] * (10 - $i);
        }
        $resto1 = $soma1 % 11;
        $dv1 = ($resto1 < 2) ? 0 : 11 - $resto1;
        if ($dv1 != $cpf[9]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma2 += $cpf[$i] * (11 - $i);
        }
        $resto2 = $soma2 % 11;
        $dv2 = ($resto2 < 2) ? 0 : 11 - $resto2;

        return $dv2 == $cpf[10];
    }

    public function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0, $pos = 5; $i < 12; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma1 += $cnpj[$i] * $pos;
        }
        $dv1 = ($soma1 % 11 < 2) ? 0 : 11 - ($soma1 % 11);
        if ($dv1 != $cnpj[12]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0, $pos = 6; $i < 13; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma2 += $cnpj[$i] * $pos;
        }
        $dv2 = ($soma2 % 11 < 2) ? 0 : 11 - ($soma2 % 11);

        return $dv2 == $cnpj[13];
    }

    public function formatarChave($chave)
    {
        if ($this->validarCPF($chave)) {
            return substr($chave, 0, 3) . '.' . substr($chave, 3, 3) . '.' . substr($chave, 6, 3) . '-' . substr($chave, 9);
        } elseif ($this->validarCNPJ($chave)) {
            return substr($chave, 0, 2) . '.' . substr($chave, 2, 3) . '.' . substr($chave, 5, 3) . '/' . substr($chave, 8, 4) . '-' . substr($chave, 12);
        } elseif (strlen($chave) === 11) {
            return '(' . substr($chave, 0, 2) . ') ' . substr($chave, 2, 5) . '-' . substr($chave, 7);
        }

        return $chave;
    }

    public function imprimir()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['anexos'] = $this->os_model->getAnexos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        if ($this->data['configuration']['pix_key']) {
            $this->data['qrCode'] = $this->os_model->getQrCode(
                $this->uri->segment(3),
                $this->data['configuration']['pix_key'],
                $this->data['emitente']
            );
            $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);
        }
        
        $this->data['imprimirAnexo'] = isset($_ENV['IMPRIMIR_ANEXOS']) ? (filter_var($_ENV['IMPRIMIR_ANEXOS'] ?? false, FILTER_VALIDATE_BOOLEAN)) : false;

        $this->load->view('os/imprimirOs', $this->data);
    }

    public function imprimirTermica()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->os_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);

        $this->load->view('os/imprimirOsTermica', $this->data);
    }

    public function imprimirProposta()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        if ($this->data['configuration']['pix_key']) {
            $this->data['qrCode'] = $this->os_model->getQrCode(
                $this->uri->segment(3),
                $this->data['configuration']['pix_key'],
                $this->data['emitente']
            );
            $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);
        }
        
        // Carregar parcelas da OS
        $this->load->model('parcelas_os_model');
        $this->data['parcelas'] = $this->parcelas_os_model->getByOs($this->uri->segment(3));

        $this->load->view('os/imprimirProposta', $this->data);
    }

    public function enviar_email()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para enviar O.S. por e-mail.');
            redirect(base_url());
        }

        $this->load->model('mapos_model');
        $this->load->model('usuarios_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        if (! isset($this->data['result']->email)) {
            $this->session->set_flashdata('error', 'O cliente não tem e-mail cadastrado.');
            redirect(site_url('os'));
        }

        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        if (! isset($this->data['emitente']->email)) {
            $this->session->set_flashdata('error', 'Efetue o cadastro dos dados de emitente');
            redirect(site_url('os'));
        }

        $idOs = $this->uri->segment(3);

        $emitente = $this->data['emitente'];
        $tecnico = $this->usuarios_model->getById($this->data['result']->usuarios_id);

        // Verificar configuração de notificação
        $ValidarEmail = false;
        if ($this->data['configuration']['os_notification'] != 'nenhum') {
            $remetentes = [];
            switch ($this->data['configuration']['os_notification']) {
                case 'todos':
                    array_push($remetentes, $this->data['result']->email);
                    array_push($remetentes, $tecnico->email);
                    array_push($remetentes, $emitente->email);
                    $ValidarEmail = true;
                    break;
                case 'cliente':
                    array_push($remetentes, $this->data['result']->email);
                    $ValidarEmail = true;
                    break;
                case 'tecnico':
                    array_push($remetentes, $tecnico->email);
                    break;
                case 'emitente':
                    array_push($remetentes, $emitente->email);
                    break;
                default:
                    array_push($remetentes, $this->data['result']->email);
                    $ValidarEmail = true;
                    break;
            }

            if ($ValidarEmail) {
                if (empty($this->data['result']->email) || ! filter_var($this->data['result']->email, FILTER_VALIDATE_EMAIL)) {
                    $this->session->set_flashdata('error', 'Por favor preencha o email do cliente');
                    redirect(site_url('os/visualizar/') . $this->uri->segment(3));
                }
            }

            $enviouEmail = $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço');

            if ($enviouEmail) {
                $this->session->set_flashdata('success', 'O email está sendo processado e será enviado em breve.');
                log_info('Enviou e-mail para o cliente: ' . $this->data['result']->nomeCliente . '. E-mail: ' . $this->data['result']->email);
                redirect(site_url('os'));
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao enviar e-mail.');
                redirect(site_url('os'));
            }
        }

        $this->session->set_flashdata('success', 'O sistema está com uma configuração ativada para não notificar. Entre em contato com o administrador.');
        redirect(site_url('os'));
    }

    private function devolucaoEstoque($id)
    {
        if ($produtos = $this->os_model->getProdutos($id)) {
            $this->load->model('produtos_model');
            if ($this->data['configuration']['control_estoque']) {
                foreach ($produtos as $p) {
                    $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, '+');
                    log_info('ESTOQUE: Produto id ' . $p->produtos_id . ' voltou ao estoque. Quantidade: ' . $p->quantidade . '. Motivo: Cancelamento/Exclusão');
                }
            }
        }
    }

    private function debitarEstoque($id)
    {
        if ($produtos = $this->os_model->getProdutos($id)) {
            $this->load->model('produtos_model');
            if ($this->data['configuration']['control_estoque']) {
                foreach ($produtos as $p) {
                    $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, '-');
                    log_info('ESTOQUE: Produto id ' . $p->produtos_id . ' baixa do estoque. Quantidade: ' . $p->quantidade . '. Motivo: Mudou status que já estava Cancelado para outro');
                }
            }
        }
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir O.S.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        $os = $this->os_model->getByIdCobrancas($id);
        if ($os == null) {
            $os = $this->os_model->getById($id);
            if ($os == null) {
                $this->session->set_flashdata('error', 'Erro ao tentar excluir OS.');
                redirect(base_url() . 'index.php/os/gerenciar/');
            }
        }

        if (isset($os->idCobranca) != null) {
            if ($os->status == 'canceled') {
                $this->os_model->delete('cobrancas', 'os_id', $id);
            } else {
                $this->session->set_flashdata('error', 'Existe uma cobrança associada a esta OS, deve cancelar e/ou excluir a cobrança primeiro!');
                redirect(site_url('os/gerenciar/'));
            }
        }

        $osStockRefund = $this->os_model->getById($id);
        //Verifica para poder fazer a devolução do produto para o estoque caso OS seja excluida.
        if (strtolower($osStockRefund->status) != 'cancelado') {
            $this->devolucaoEstoque($id);
        }

        $this->os_model->delete('servicos_os', 'os_id', $id);
        $this->os_model->delete('produtos_os', 'os_id', $id);
        $this->os_model->delete('anexos', 'os_id', $id);
        $this->os_model->delete('os', 'idOs', $id);
        if ((int) $os->faturado === 1) {
            $this->os_model->delete('lancamentos', 'descricao', "Fatura de OS - #${id}");
        }

        log_info('Removeu uma OS. ID: ' . $id);
        $this->session->set_flashdata('success', 'OS excluída com sucesso!');
        redirect(site_url('os/gerenciar/'));
    }

    public function autoCompleteProduto()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteProduto($q);
        }
    }

    public function autoCompleteProdutoSaida()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteProdutoSaida($q);
        }
    }

    public function autoCompleteCliente()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteCliente($q);
        }
    }

    public function cadastrarClienteRapido()
    {
        // Desabilitar CSRF para este método específico
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->config->set_item('csrf_protection', false);
        
        // Retornar teste imediato para verificar se o método é chamado
        // TODO: Remover após teste
        if (isset($_GET['test'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Método acessível!',
                    'post' => $_POST,
                    'session' => $this->session->userdata('logado') ? 'logado' : 'não logado'
                ]));
        }
        
        // Seguir o mesmo padrão do método adicionarProduto que funciona
        $this->load->model('clientes_model');
        
        $nomeCliente = $this->input->post('nomeCliente');
        
        if (empty($nomeCliente) || trim($nomeCliente) == '') {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'O nome do cliente é obrigatório.'
                ]));
        }

        // Preparar dados - apenas nome obrigatório, outros campos opcionais
        $telefone = $this->input->post('telefone');
        if (empty($telefone) || trim($telefone) == '') {
            $telefone = '00000000000';
        }
        
        $celular = $this->input->post('celular');
        if (empty($celular) || trim($celular) == '') {
            $celular = null;
        }
        
        $email = trim($this->input->post('email'));
        if (empty($email) || $email === '') {
            // Gerar email temporário único se não fornecido
            $email = 'cliente_' . time() . '_' . rand(1000, 9999) . '@temp.mapos.com';
        }
        
        // Limpar emails de exemplo ou inválidos
        if ($email && (strpos($email, '@exemplo.com') !== false || $email === '...')) {
            $email = 'cliente_' . time() . '_' . rand(1000, 9999) . '@temp.mapos.com';
        }
        
        // Verificar se email já existe (apenas para emails reais, não temporários)
        if ($email && strpos($email, '@temp.mapos.com') === false && $this->clientes_model->emailExists($email)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Este e-mail já está sendo utilizado por outro cliente.'
                ]));
        }

        // Processar documento
        $documento_post = trim($this->input->post('documento'));
        $documento = null;
        $pessoa_fisica = 1; // Padrão pessoa física
        
        if (!empty($documento_post)) {
            // Remover formatação
            $documento_limpo = preg_replace('/[^0-9A-Za-z]/', '', $documento_post);
            
            // Determinar se é CPF ou CNPJ baseado no tamanho
            if (strlen($documento_limpo) == 11) {
                // CPF
                $documento = $documento_limpo;
                $pessoa_fisica = 1;
            } elseif (strlen($documento_limpo) == 14) {
                // CNPJ
                $documento = $documento_limpo;
                $pessoa_fisica = 0;
            } else {
                // Documento inválido, gerar temporário
                $documento = '00000000000';
            }
        } else {
            // Gerar documento temporário único para evitar duplicatas
            $documento = '00000000000'; // CPF temporário para pessoa física
            
            // Verificar se já existe cliente com este documento temporário
            $this->db->where('documento', $documento);
            $existe = $this->db->get('clientes')->num_rows();
            if ($existe > 0) {
                // Gerar documento único baseado em timestamp
                $documento = '000' . substr(time(), -8);
            }
        }
        
        // Gerar senha temporária
        $senha = password_hash($documento, PASSWORD_DEFAULT);

        $data = [
            'nomeCliente' => trim($nomeCliente),
            'contato' => null,
            'pessoa_fisica' => $pessoa_fisica,
            'documento' => $documento,
            'telefone' => $telefone,
            'celular' => $celular,
            'email' => $email,
            'senha' => $senha,
            'rua' => $this->input->post('rua') ?: null,
            'numero' => $this->input->post('numero') ?: null,
            'complemento' => $this->input->post('complemento') ?: null,
            'bairro' => $this->input->post('bairro') ?: null,
            'cidade' => $this->input->post('cidade') ?: null,
            'estado' => $this->input->post('estado') ?: null,
            'cep' => $this->input->post('cep') ?: null,
            'dataCadastro' => date('Y-m-d'),
            'fornecedor' => 0,
        ];

        $idCliente = $this->clientes_model->add('clientes', $data);
        
        if ($idCliente) {
            $cliente = $this->clientes_model->getById($idCliente);
            log_info('Cadastrou um cliente rápido da tela de OS. ID: ' . $idCliente);
            
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Cliente cadastrado com sucesso!',
                    'cliente' => [
                        'idClientes' => $cliente->idClientes,
                        'nomeCliente' => $cliente->nomeCliente
                    ],
                    'csrf_token' => $this->security->get_csrf_hash()
                ]));
        } else {
            $error = $this->db->error();
            
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Erro ao cadastrar cliente. Tente novamente.'
                ]));
        }
    }

    public function autoCompleteUsuario()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteUsuario($q);
        }
    }

    public function autoCompleteTermoGarantia()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteTermoGarantia($q);
        }
    }

    public function autoCompleteServico()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteServico($q);
        }
    }

    public function adicionarProduto()
    {
        $this->load->library('form_validation');

        if ($this->form_validation->run('adicionar_produto_os') === false) {
            $errors = validation_errors();

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode($errors));
        }

        $preco = $this->input->post('preco');
        $quantidade = $this->input->post('quantidade');
        $subtotal = $preco * $quantidade;
        $produto = $this->input->post('idProduto');
        $data = [
            'quantidade' => $quantidade,
            'subTotal' => $subtotal,
            'produtos_id' => $produto,
            'preco' => $preco,
            'os_id' => $this->input->post('idOsProduto'),
        ];

        $id = $this->input->post('idOsProduto');
        $os = $this->os_model->getById($id);
        if ($os == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar inserir produto na OS.');
            redirect(base_url() . 'index.php/os/gerenciar/');
        }

        // Verificar se o produto já existe nesta OS
        $produtoExistente = $this->db->get_where('produtos_os', [
            'os_id' => $id,
            'produtos_id' => $produto
        ])->row();

        if ($produtoExistente) {
            // Produto já existe, atualizar quantidade e subtotal
            $novaQuantidade = $produtoExistente->quantidade + $quantidade;
            $novoSubtotal = $preco * $novaQuantidade;

            $this->db->where('idProdutos_os', $produtoExistente->idProdutos_os);
            $this->db->update('produtos_os', [
                'quantidade' => $novaQuantidade,
                'subTotal' => $novoSubtotal,
                'preco' => $preco
            ]);

            $lastId = $produtoExistente->idProdutos_os;
            $produtoAdicionado = false;

            // Só consome estoque se o status da OS exigir
            if ($this->statusConsumeEstoque($os->status)) {
                $this->load->model('produtos_model');

                if ($this->data['configuration']['control_estoque']) {
                    // Descontar apenas a quantidade ADICIONAL
                    $this->produtos_model->updateEstoque($produto, $quantidade, '-');
                }

                // Garantir que está marcado como consumido
                if ($produtoExistente->estoque_consumido == 0) {
                    $this->db->where('idProdutos_os', $lastId);
                    $this->db->update('produtos_os', ['estoque_consumido' => 1]);
                }

                log_info("Produto ATUALIZADO e estoque CONSUMIDO. OS: {$id}, Produto: {$produto}, Qtd adicional: {$quantidade}, Total: {$novaQuantidade}, Status: {$os->status}");
            } else {
                log_info("Produto ATUALIZADO SEM consumir estoque. OS: {$id}, Produto: {$produto}, Qtd adicional: {$quantidade}, Total: {$novaQuantidade}, Status: {$os->status}");
            }
        } else {
            // Produto não existe, adicionar novo
            $produtoAdicionado = true;
            
            if ($this->os_model->add('produtos_os', $data) == true) {
                $lastId = $this->db->insert_id();
            } else {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(500)
                    ->set_output(json_encode(['result' => false, 'message' => 'Erro ao adicionar produto']));
            }
        }

        // Lógica de estoque para produtos novos
        if ($produtoAdicionado) {
            // Só consome estoque se o status da OS exigir
            if ($this->statusConsumeEstoque($os->status)) {
                $this->load->model('produtos_model');

                if ($this->data['configuration']['control_estoque']) {
                    $this->produtos_model->updateEstoque($produto, $quantidade, '-');
                }

                // Marcar que o estoque foi consumido
                $this->db->where('idProdutos_os', $lastId);
                $this->db->update('produtos_os', ['estoque_consumido' => 1]);

                log_info("Produto adicionado e estoque CONSUMIDO. OS: {$id}, Produto: {$produto}, Qtd: {$quantidade}, Status: {$os->status}");
            } else {
                log_info("Produto adicionado SEM consumir estoque. OS: {$id}, Produto: {$produto}, Qtd: {$quantidade}, Status: {$os->status}");
            }
        }

        // Resetar desconto
        $this->db->set('desconto', 0.00);
        $this->db->set('valor_desconto', 0.00);
        $this->db->set('tipo_desconto', null);
        $this->db->where('idOs', $id);
        $this->db->update('os');

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['result' => true]));
    }

    public function excluirProduto()
    {
        $id = $this->input->post('idProduto');
        $idOs = $this->input->post('idOs');

        $os = $this->os_model->getById($idOs);
        if ($os == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir produto na OS.');
            redirect(base_url() . 'index.php/os/gerenciar/');
        }

        // Buscar informações do produto ANTES de excluir
        $produtoOs = $this->db->get_where('produtos_os', ['idProdutos_os' => $id])->row();

        if ($this->os_model->delete('produtos_os', 'idProdutos_os', $id) == true) {
            $quantidade = $this->input->post('quantidade');
            $produto = $this->input->post('produto');

            // Só devolve estoque se o estoque havia sido consumido
            if ($produtoOs && $produtoOs->estoque_consumido == 1) {
                $this->load->model('produtos_model');

                if ($this->data['configuration']['control_estoque']) {
                    $this->produtos_model->updateEstoque($produto, $quantidade, '+');
                }

                log_info("Produto removido e estoque DEVOLVIDO. OS: {$idOs}, Produto: {$produto}, Qtd: {$quantidade}");
            } else {
                log_info("Produto removido SEM devolver estoque (não havia sido consumido). OS: {$idOs}, Produto: {$produto}");
            }

            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $idOs);
            $this->db->update('os');

            log_info('Removeu produto de uma OS. ID (OS): ' . $idOs);

            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
    }

    public function adicionarServico()
    {
        $this->load->library('form_validation');
        
        // Verificar se é um serviço customizado (texto livre)
        $outrosProdutosServicos = $this->input->post('outros_produtos_servicos');
        $isCustomizado = !empty(trim($outrosProdutosServicos));
        
        // Se for customizado, validar apenas os campos necessários
        if ($isCustomizado) {
            // Usar preço e quantidade específicos do campo "outros"
            $preco = $this->input->post('preco_outros');
            $quantidade = $this->input->post('quantidade_outros');
            
            // Converter preço de formato brasileiro (0,00) para numérico
            if (!empty($preco)) {
                $preco = str_replace('.', '', $preco);
                $preco = str_replace(',', '.', $preco);
            }
            
            if (empty($preco) || !is_numeric($preco) || floatval($preco) <= 0) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'message' => 'Preço é obrigatório e deve ser maior que zero.']));
            }
            
            if (empty($quantidade) || !is_numeric($quantidade) || floatval($quantidade) <= 0) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'message' => 'Quantidade é obrigatória e deve ser maior que zero.']));
            }
        } else {
            // Validação normal para serviços cadastrados
            if ($this->form_validation->run('adicionar_servico_os') === false) {
                $errors = validation_errors();

                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode($errors));
            }
        }

        if ($isCustomizado) {
            $preco = floatval($preco);
            $quantidade = floatval($quantidade);
        } else {
            $preco = floatval($this->input->post('preco'));
            $quantidade = floatval($this->input->post('quantidade'));
        }
        
        $data = [
            'quantidade' => $quantidade,
            'preco' => $preco,
            'os_id' => $this->input->post('idOsServico'),
            'subTotal' => $preco * $quantidade,
        ];
        
        // Para serviços customizados, não incluir servicos_id (será NULL)
        // Para serviços cadastrados, incluir servicos_id
        if (!$isCustomizado) {
            $data['servicos_id'] = $this->input->post('idServico');
        }
        
        // Para serviços customizados, usar o campo detalhes para armazenar a descrição
        if ($isCustomizado) {
            $fields = $this->db->field_data('servicos_os');
            $field_exists = false;
            foreach ($fields as $field) {
                if ($field->name === 'detalhes') {
                    $field_exists = true;
                    break;
                }
            }
            if ($field_exists) {
                $data['detalhes'] = trim($outrosProdutosServicos);
            }
        } else {
            // Adicionar detalhes apenas se o campo existir no banco
            $detalhes = $this->input->post('detalhes');
            if ($detalhes !== null && $detalhes !== '') {
                $fields = $this->db->field_data('servicos_os');
                $field_exists = false;
                foreach ($fields as $field) {
                    if ($field->name === 'detalhes') {
                        $field_exists = true;
                        break;
                    }
                }
                if ($field_exists) {
                    $data['detalhes'] = $detalhes;
                }
            }
        }

        if ($this->os_model->add('servicos_os', $data) == true) {
            log_info('Adicionou serviço a uma OS. ID (OS): ' . $this->input->post('idOsServico'));

            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $this->input->post('idOsServico'));
            $this->db->update('os');

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(['result' => true]));
        } else {
            $error = $this->db->error();
            $error_message = 'Erro ao adicionar serviço.';
            if (!empty($error['message'])) {
                $error_message .= ' ' . $error['message'];
            }
            
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => $error_message
                ]));
        }
    }

    public function adicionarOutros()
    {
        $os_id = $this->input->post('idOsOutros');
        $descricao = $this->input->post('descricao');
        $preco = $this->input->post('preco');
        
        if (empty($os_id) || !is_numeric($os_id)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => 'ID da OS inválido']));
        }
        
        if (empty(trim($descricao))) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => 'Descrição é obrigatória']));
        }
        
        // Converter preço de formato brasileiro (0,00) para numérico
        if (!empty($preco)) {
            $preco = str_replace('.', '', $preco);
            $preco = str_replace(',', '.', $preco);
        }
        
        if (empty($preco) || !is_numeric($preco) || floatval($preco) <= 0) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => 'Preço é obrigatório e deve ser maior que zero']));
        }
        
        $this->load->model('outros_produtos_servicos_os_model');
        
        $data = [
            'os_id' => $os_id,
            'descricao' => $descricao,
            'preco' => floatval($preco)
        ];
        
        if ($this->outros_produtos_servicos_os_model->add($data)) {
            log_info('Adicionou outros produtos/serviços à OS. ID (OS): ' . $os_id);
            
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(['result' => true, 'message' => 'Item adicionado com sucesso!']));
        } else {
            $error = $this->db->error();
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => 'Erro ao adicionar item: ' . ($error['message'] ?? 'Erro desconhecido')
                ]));
        }
    }
    
    public function excluirOutros()
    {
        $id = $this->input->post('id');
        $idOs = $this->input->post('idOs');
        
        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['result' => false, 'message' => 'ID inválido']);
            return;
        }
        
        $this->load->model('outros_produtos_servicos_os_model');
        
        if ($this->outros_produtos_servicos_os_model->delete($id)) {
            log_info('Removeu outros produtos/serviços da OS. ID (OS): ' . $idOs);
            echo json_encode(['result' => true, 'message' => 'Item excluído com sucesso!']);
        } else {
            echo json_encode(['result' => false, 'message' => 'Erro ao excluir item']);
        }
    }

    public function excluirServico()
    {
        $ID = $this->input->post('idServico');
        $idOs = $this->input->post('idOs');

        if ($this->os_model->delete('servicos_os', 'idServicos_os', $ID) == true) {
            log_info('Removeu serviço de uma OS. ID (OS): ' . $idOs);
            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $idOs);
            $this->db->update('os');
            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
    }

    /**
     * Edita o preço de um serviço na OS sem alterar o preço original do serviço
     */
    public function editarPrecoServico()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('idServicos_os', 'ID do Serviço', 'required|integer');
        $this->form_validation->set_rules('preco', 'Preço', 'required|numeric');
        $this->form_validation->set_rules('idOs', 'ID da OS', 'required|integer');

        if ($this->form_validation->run() === false) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => validation_errors()
                ]));
        }

        $idServicosOs = $this->input->post('idServicos_os');
        $idOs = $this->input->post('idOs');
        $preco = $this->input->post('preco');
        
        // Remover formatação do preço (vírgulas, pontos, etc)
        $preco = str_replace(',', '.', $preco);
        $preco = preg_replace('/[^0-9.]/', '', $preco);
        
        // Buscar quantidade atual do serviço na OS
        $this->db->where('idServicos_os', $idServicosOs);
        $servicoOs = $this->db->get('servicos_os')->row();
        
        if (!$servicoOs) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => 'Serviço não encontrado na OS.'
                ]));
        }

        $quantidade = $servicoOs->quantidade ?: 1;
        $subTotal = $preco * $quantidade;

        // Atualizar preço, subtotal e detalhes na tabela servicos_os
        // O preço original do serviço (servicos.preco) permanece inalterado
        $data = [
            'preco' => $preco,
            'subTotal' => $subTotal
        ];
        
        // Adicionar detalhes apenas se o campo existir no banco
        $detalhes = $this->input->post('detalhes');
        if ($detalhes !== null) {
            // Verificar se o campo existe antes de adicionar
            $fields = $this->db->field_data('servicos_os');
            $field_exists = false;
            foreach ($fields as $field) {
                if ($field->name === 'detalhes') {
                    $field_exists = true;
                    break;
                }
            }
            if ($field_exists) {
                $data['detalhes'] = $detalhes ?: null;
            }
        }

        if ($this->os_model->edit('servicos_os', $data, 'idServicos_os', $idServicosOs)) {
            log_info('Editou serviço na OS. ID (OS): ' . $idOs . ', ID (Serviço OS): ' . $idServicosOs . ', Novo Preço: ' . $preco);

            // Resetar desconto da OS quando o preço é alterado
            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $idOs);
            $this->db->update('os');

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'result' => true,
                    'message' => 'Serviço atualizado com sucesso!'
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => 'Erro ao atualizar serviço.'
                ]));
        }
    }

    /**
     * Cria um novo serviço rapidamente via AJAX (usado na edição de OS)
     */
    public function criarServicoRapido()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aServico')) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403)
                ->set_output(json_encode(['result' => false, 'message' => 'Você não tem permissão para adicionar serviços.']));
        }

        $this->load->library('form_validation');
        $this->load->model('servicos_model');

        // Regras de validação
        $this->form_validation->set_rules('nome', 'Nome', 'required|trim');
        $this->form_validation->set_rules('preco', 'Preço', 'required|trim');

        if ($this->form_validation->run() === false) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => validation_errors()]));
        }

        $preco = $this->input->post('preco');
        $preco = str_replace(',', '', $preco);

        $data = [
            'nome' => $this->input->post('nome'),
            'descricao' => $this->input->post('descricao') ?: '',
            'preco' => $preco,
        ];

        if ($this->servicos_model->add('servicos', $data) == true) {
            // Buscar o serviço recém-criado
            $this->db->where('nome', $data['nome']);
            $this->db->order_by('idServicos', 'DESC');
            $this->db->limit(1);
            $servico = $this->db->get('servicos')->row();

            log_info('Criou serviço rapidamente. ID: ' . $servico->idServicos);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'result' => true,
                    'message' => 'Serviço criado com sucesso!',
                    'servico' => [
                        'id' => $servico->idServicos,
                        'nome' => $servico->nome,
                        'preco' => $servico->preco
                    ]
                ]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode(['result' => false, 'message' => 'Erro ao criar serviço.']));
        }
    }

    public function anexar()
    {
        $this->load->library('upload');
        $this->load->library('image_lib');

        $directory = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . date('m-Y') . DIRECTORY_SEPARATOR . 'OS-' . $this->input->post('idOsServico');

        // If it exist, check if it's a directory
        if (! is_dir($directory . DIRECTORY_SEPARATOR . 'thumbs')) {
            // make directory for images and thumbs
            try {
                mkdir($directory . DIRECTORY_SEPARATOR . 'thumbs', 0755, true);
            } catch (Exception $e) {
                echo json_encode(['result' => false, 'mensagem' => $e->getMessage()]);
                exit();
            }
        }

        $upload_conf = [
            'upload_path' => $directory,
            'allowed_types' => 'jpg|png|gif|jpeg|JPG|PNG|GIF|JPEG|pdf|PDF|cdr|CDR|docx|DOCX|txt', // formatos permitidos para anexos de os
            'max_size' => 0,
        ];

        $this->upload->initialize($upload_conf);

        foreach ($_FILES['userfile'] as $key => $val) {
            $i = 1;
            foreach ($val as $v) {
                $field_name = 'file_' . $i;
                $_FILES[$field_name][$key] = $v;
                $i++;
            }
        }
        unset($_FILES['userfile']);

        $error = [];
        $success = [];

        foreach ($_FILES as $field_name => $file) {
            if (! $this->upload->do_upload($field_name)) {
                $error['upload'][] = $this->upload->display_errors();
            } else {
                $upload_data = $this->upload->data();

                // Gera um nome de arquivo aleatório mantendo a extensão original
                $new_file_name = uniqid() . '.' . pathinfo($upload_data['file_name'], PATHINFO_EXTENSION);
                $new_file_path = $upload_data['file_path'] . $new_file_name;

                rename($upload_data['full_path'], $new_file_path);

                if ($upload_data['is_image'] == 1) {
                    $resize_conf = [
                        'source_image' => $new_file_path,
                        'new_image' => $upload_data['file_path'] . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_' . $new_file_name,
                        'width' => 200,
                        'height' => 125,
                    ];

                    $this->image_lib->initialize($resize_conf);

                    if (! $this->image_lib->resize()) {
                        $error['resize'][] = $this->image_lib->display_errors();
                    } else {
                        $success[] = $upload_data;
                        $this->load->model('Os_model');
                        $result = $this->Os_model->anexar($this->input->post('idOsServico'), $new_file_name, base_url('assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . date('m-Y') . DIRECTORY_SEPARATOR . 'OS-' . $this->input->post('idOsServico')), 'thumb_' . $new_file_name, $directory);
                        if (! $result) {
                            $error['db'][] = 'Erro ao inserir no banco de dados.';
                        }
                    }
                } else {
                    $success[] = $upload_data;

                    $this->load->model('Os_model');

                    $result = $this->Os_model->anexar($this->input->post('idOsServico'), $new_file_name, base_url('assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . date('m-Y') . DIRECTORY_SEPARATOR . 'OS-' . $this->input->post('idOsServico')), '', $directory);
                    if (! $result) {
                        $error['db'][] = 'Erro ao inserir no banco de dados.';
                    }
                }
            }
        }

        if (count($error) > 0) {
            echo json_encode(['result' => false, 'mensagem' => 'Ocorreu um erro ao processar os arquivos.', 'errors' => $error]);
        } else {
            log_info('Adicionou anexo(s) a uma OS. ID (OS): ' . $this->input->post('idOsServico'));
            echo json_encode(['result' => true, 'mensagem' => 'Arquivo(s) anexado(s) com sucesso.']);
        }
    }

    public function excluirAnexo($id = null)
    {
        if ($id == null || ! is_numeric($id)) {
            echo json_encode(['result' => false, 'mensagem' => 'Erro ao tentar excluir anexo.']);
        } else {
            $this->db->where('idAnexos', $id);
            $file = $this->db->get('anexos', 1)->row();
            $idOs = $this->input->post('idOs');

            unlink($file->path . DIRECTORY_SEPARATOR . $file->anexo);

            if ($file->thumb != null) {
                unlink($file->path . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $file->thumb);
            }

            if ($this->os_model->delete('anexos', 'idAnexos', $id) == true) {
                log_info('Removeu anexo de uma OS. ID (OS): ' . $idOs);
                echo json_encode(['result' => true, 'mensagem' => 'Anexo excluído com sucesso.']);
            } else {
                echo json_encode(['result' => false, 'mensagem' => 'Erro ao tentar excluir anexo.']);
            }
        }
    }

    public function downloadanexo($id = null)
    {
        if ($id != null && is_numeric($id)) {
            $this->db->where('idAnexos', $id);
            $file = $this->db->get('anexos', 1)->row();

            $this->load->library('zip');
            $path = $file->path;
            $this->zip->read_file($path . '/' . $file->anexo);
            $this->zip->download('file' . date('d-m-Y-H.i.s') . '.zip');
        }
    }

    public function adicionarDesconto()
    {
        if ($this->input->post('desconto') == '') {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['messages' => 'Campo desconto vazio']));
        } else {
            $idOs = $this->input->post('idOs');
            $data = [
                'tipo_desconto' => $this->input->post('tipoDesconto'),
                'desconto' => $this->input->post('desconto'),
                'valor_desconto' => $this->input->post('resultado'),
            ];
            $editavel = $this->os_model->isEditable($idOs);
            if (! $editavel) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages', 'Desconto não pode ser adiciona. Os não ja Faturada/Cancelada']));
            }
            if ($this->os_model->edit('os', $data, 'idOs', $idOs) == true) {
                log_info('Adicionou um desconto na OS. ID: ' . $idOs);

                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(201)
                    ->set_output(json_encode(['result' => true, 'messages' => 'Desconto adicionado com sucesso!']));
            } else {
                log_info('Ocorreu um erro ao tentar adiciona desconto a OS: ' . $idOs);

                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages', 'Ocorreu um erro ao tentar adiciona desconto a OS.']));
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['result' => false, 'messages', 'Ocorreu um erro ao tentar adiciona desconto a OS.']));
    }

    public function faturar()
    {
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('receita') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $vencimento = $this->input->post('vencimento');
            $recebimento = $this->input->post('recebimento');

            try {
                $vencimento = DateTime::createFromFormat('d/m/Y', $vencimento)->format('Y-m-d');
                if ($recebimento != null) {
                    $recebimento = DateTime::createFromFormat('d/m/Y', $recebimento)->format('Y-m-d');
                }
            } catch (Exception $e) {
                $vencimento = date('Y-m-d');
            }

            $os_id = $this->input->post('os_id');
            $valorTotalData = $this->os_model->valorTotalOS($os_id);

            $valorTotalServico = $valorTotalData['totalServico'];
            $valorTotalProduto = $valorTotalData['totalProdutos'];
            $valorDesconto = $valorTotalData['valor_desconto'];

            $valorTotal = $valorTotalServico + $valorTotalProduto;
            $valorTotalComDesconto = $valorTotal - $valorDesconto;

            $data = [
                'descricao' => set_value('descricao'),
                'valor' => $valorTotal,
                'tipo_desconto' => 'real',
                'desconto' => ($valorDesconto > 0) ? $valorTotalComDesconto : 0,
                'valor_desconto' => ($valorDesconto > 0) ? $valorDesconto : $valorTotal,
                'clientes_id' => $this->input->post('clientes_id'),
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento,
                'baixado' => $this->input->post('recebido') ?: 0,
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo'),
                'observacoes' => set_value('observacoes'),
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            $this->db->trans_start();

            $editavel = $this->os_model->isEditable($os_id);
            if (!$editavel) {
                $this->db->trans_rollback();
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false]));
            }

            if ($this->os_model->add('lancamentos', $data)) {
                $this->db->set('faturado', 1);
                $this->db->set('valorTotal', $valorTotal);

                if ($valorDesconto > 0) {
                    $this->db->set('desconto', $valorTotalComDesconto);
                    $this->db->set('valor_desconto', $valorDesconto);
                } else {
                    $this->db->set('desconto', 0);
                    $this->db->set('valor_desconto', $valorTotal);
                }

                $this->db->set('status', 'Faturado');
                $this->db->where('idOs', $os_id);
                $this->db->update('os');

                log_info('Faturou uma OS. ID: ' . $os_id);

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar OS.');
                    $json = ['result' => false];
                } else {
                    $this->session->set_flashdata('success', 'OS faturada com sucesso!');
                    $json = ['result' => true];
                }
            } else {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar OS.');
                $json = ['result' => false];
            }

            echo json_encode($json);
            exit();
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar OS.');
        $json = ['result' => false];
        echo json_encode($json);
    }

    private function enviarOsPorEmail($idOs, $remetentes, $assunto)
    {
        $dados = [];

        $this->load->model('mapos_model');
        $dados['result'] = $this->os_model->getById($idOs);
        if (! isset($dados['result']->email)) {
            return false;
        }

        $dados['produtos'] = $this->os_model->getProdutos($idOs);
        $dados['servicos'] = $this->os_model->getServicos($idOs);
        $dados['emitente'] = $this->mapos_model->getEmitente();
        $emitente = $dados['emitente'];
        if (! isset($emitente->email)) {
            return false;
        }

        $html = $this->load->view('os/emails/os', $dados, true);

        $this->load->model('email_model');

        $remetentes = array_unique($remetentes);
        foreach ($remetentes as $remetente) {
            if ($remetente) {
                $headers = ['From' => $emitente->email, 'Subject' => $assunto, 'Return-Path' => ''];
                $email = [
                    'to' => $remetente,
                    'message' => $html,
                    'status' => 'pending',
                    'date' => date('Y-m-d H:i:s'),
                    'headers' => serialize($headers),
                ];
                $this->email_model->add('email_queue', $email);
            } else {
                log_info('Email não adicionado a Lista de envio de e-mails. Verifique se o remetente esta cadastrado. OS ID: ' . $idOs);
            }
        }

        return true;
    }

    public function adicionarAnotacao()
    {
        $this->load->library('form_validation');
        if ($this->form_validation->run('anotacoes_os') == false) {
            echo json_encode(validation_errors());
        } else {
            $data = [
                'anotacao' => '[' . $this->session->userdata('nome_admin') . '] ' . $this->input->post('anotacao'),
                'data_hora' => date('Y-m-d H:i:s'),
                'os_id' => $this->input->post('os_id'),
            ];

            if ($this->os_model->add('anotacoes_os', $data) == true) {
                log_info('Adicionou anotação a uma OS. ID (OS): ' . $this->input->post('os_id'));
                echo json_encode(['result' => true]);
            } else {
                echo json_encode(['result' => false]);
            }
        }
    }

    public function excluirAnotacao()
    {
        $id = $this->input->post('idAnotacao');
        $idOs = $this->input->post('idOs');

        if ($this->os_model->delete('anotacoes_os', 'idAnotacoes', $id) == true) {
            log_info('Removeu anotação de uma OS. ID (OS): ' . $idOs);
            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
    }

    /**
     * Verifica se o status da OS deve consumir estoque
     * Status que NÃO consomem: Orçamento, Negociação
     * Status que consomem: Todos os outros (Aberto, Aprovado, Em Andamento, etc.)
     */
    private function statusConsumeEstoque($status)
    {
        $statusQueNaoConsomem = ['Orçamento', 'Negociação'];
        return !in_array($status, $statusQueNaoConsomem);
    }

    /**
     * Verifica se o status da OS inicia a contagem da garantia
     * Garantia só começa a contar quando o serviço é realmente iniciado/aprovado
     * Status que iniciam: Aprovado, Em Andamento, Aguardando Peças, Finalizado, Faturado
     * Status que NÃO iniciam: Orçamento, Negociação, Aberto, Cancelado
     */
    private function statusIniciaGarantia($status)
    {
        $statusQueIniciamGarantia = [
            'Aprovado',
            'Em Andamento',
            'Aguardando Peças',
            'Finalizado',
            'Faturado'
        ];
        return in_array($status, $statusQueIniciamGarantia);
    }

    /**
     * Atualiza o status de uma OS via AJAX
     * Gerencia estoque e garantia baseado na mudança de status
     */
    public function atualizarStatus()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'Você não tem permissão para editar OS']));
            return;
        }

        $idOs = $this->input->post('idOs');
        $novoStatus = $this->input->post('status');

        if (!$idOs || !$novoStatus) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'Dados inválidos']));
            return;
        }

        // Buscar OS atual
        $os = $this->os_model->getById($idOs);
        if (!$os) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'OS não encontrada']));
            return;
        }

        $statusAntigo = $os->status;

        // Verificar mudanças de estoque necessárias
        $statusAntigoConsome = $this->statusConsumeEstoque($statusAntigo);
        $novoStatusConsome = $this->statusConsumeEstoque($novoStatus);

        // Caso 1: Não consumia, agora consome (Orçamento → Aprovado)
        if (!$statusAntigoConsome && $novoStatusConsome) {
            $this->consumirEstoqueOS($idOs);
            log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque CONSUMIDO. OS: {$idOs}");
        }
        // Caso 2: Consumia, agora não consome (Aprovado → Cancelado)
        elseif ($statusAntigoConsome && !$novoStatusConsome) {
            $this->devolverEstoqueOS($idOs);
            log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque DEVOLVIDO. OS: {$idOs}");
        }
        // Caso 3: Sem mudança no comportamento de estoque
        else {
            log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - SEM alteração de estoque. OS: {$idOs}");
        }

        // Verificar se deve iniciar a garantia
        $statusAntigoIniciaGarantia = $this->statusIniciaGarantia($statusAntigo);
        $novoStatusIniciaGarantia = $this->statusIniciaGarantia($novoStatus);

        $data = array('status' => $novoStatus);

        // Se mudou para um status que inicia garantia E ainda não tem data de início da garantia
        if ($novoStatusIniciaGarantia && !$statusAntigoIniciaGarantia) {
            // Verificar se já existe data de início (não sobrescrever se já foi definida)
            if (empty($os->dataInicioGarantia) && !empty($os->garantia) && $os->garantia > 0) {
                // Definir data de início da garantia como hoje
                $data['dataInicioGarantia'] = date('Y-m-d');
                log_info("Garantia INICIADA. OS: {$idOs}, Data início: " . date('Y-m-d') . ", Dias: {$os->garantia}");
            }
        }

        if ($this->os_model->edit('os', $data, 'idOs', $idOs) == true) {
            log_info('Atualizou status da OS. ID: ' . $idOs . ' | Novo status: ' . $novoStatus);
            
            // Gerar lançamento automático se mudou para Finalizado ou Faturado
            if (($novoStatus === 'Finalizado' || $novoStatus === 'Faturado') && 
                ($statusAntigo !== 'Finalizado' && $statusAntigo !== 'Faturado')) {
                
                // Verificar se já tem lançamento vinculado
                if (empty($os->lancamento)) {
                    // Verificar se tem forma de pagamento configurada
                    if (!empty($os->forma_pgto)) {
                        $this->gerarLancamentoAutomatico($idOs);
                    }
                }
            }
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => true, 'message' => 'Status atualizado com sucesso!']));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'Erro ao atualizar status']));
        }
    }

    /**
     * Consome estoque de todos os produtos de uma OS
     */
    private function consumirEstoqueOS($idOs)
    {
        $query = "SELECT po.idProdutos_os, po.produtos_id, po.quantidade 
                  FROM produtos_os po 
                  WHERE po.os_id = ? AND po.estoque_consumido = 0";

        $produtos = $this->db->query($query, [$idOs])->result();

        if (!$produtos || count($produtos) == 0) {
            log_info("Nenhum produto para consumir estoque. OS: {$idOs}");
            return true;
        }

        $this->load->model('produtos_model');
        $produtosProcessados = 0;

        foreach ($produtos as $produto) {
            if ($this->data['configuration']['control_estoque']) {
                $this->produtos_model->updateEstoque($produto->produtos_id, $produto->quantidade, '-');
            }

            $this->db->where('idProdutos_os', $produto->idProdutos_os);
            $this->db->update('produtos_os', ['estoque_consumido' => 1]);

            log_info("Estoque consumido: Produto {$produto->produtos_id}, Qtd: {$produto->quantidade}, OS: {$idOs}");
            $produtosProcessados++;
        }

        log_info("Total de produtos com estoque consumido: {$produtosProcessados}. OS: {$idOs}");
        return true;
    }

    /**
     * Devolve estoque de todos os produtos de uma OS
     */
    private function devolverEstoqueOS($idOs)
    {
        $query = "SELECT po.idProdutos_os, po.produtos_id, po.quantidade 
                  FROM produtos_os po 
                  WHERE po.os_id = ? AND po.estoque_consumido = 1";

        $produtos = $this->db->query($query, [$idOs])->result();

        if (!$produtos || count($produtos) == 0) {
            log_info("Nenhum produto para devolver estoque. OS: {$idOs}");
            return true;
        }

        $this->load->model('produtos_model');
        $produtosProcessados = 0;

        foreach ($produtos as $produto) {
            if ($this->data['configuration']['control_estoque']) {
                $this->produtos_model->updateEstoque($produto->produtos_id, $produto->quantidade, '+');
            }

            $this->db->where('idProdutos_os', $produto->idProdutos_os);
            $this->db->update('produtos_os', ['estoque_consumido' => 0]);

            log_info("Estoque devolvido: Produto {$produto->produtos_id}, Qtd: {$produto->quantidade}, OS: {$idOs}");
            $produtosProcessados++;
        }

        log_info("Total de produtos com estoque devolvido: {$produtosProcessados}. OS: {$idOs}");
        return true;
    }

    /**
     * Gera lançamento financeiro usando parcelas configuradas
     */
    public function gerarLancamentoParcelas()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            echo json_encode(['result' => false, 'message' => 'Você não tem permissão']);
            return;
        }

        $idOs = $this->input->post('idOs');
        $parcelasJson = $this->input->post('parcelas');
        $criarLancamento = $this->input->post('criar_lancamento');

        if (!$idOs) {
            echo json_encode(['result' => false, 'message' => 'ID da OS inválido']);
            return;
        }

        // Buscar OS
        $os = $this->os_model->getById($idOs);
        if (!$os) {
            echo json_encode(['result' => false, 'message' => 'OS não encontrada']);
            return;
        }

        // Verificar se já tem lançamento vinculado
        if (!empty($os->lancamento)) {
            echo json_encode(['result' => false, 'message' => 'Esta OS já possui um lançamento financeiro vinculado']);
            return;
        }

        // Decodificar parcelas
        $parcelas = json_decode($parcelasJson, true);
        if (!is_array($parcelas) || count($parcelas) === 0) {
            echo json_encode(['result' => false, 'message' => 'Nenhuma parcela configurada']);
            return;
        }

        // Se não deve criar lançamento, apenas atualizar parcelas e retornar
        if (!$criarLancamento || $criarLancamento == '0') {
            $this->load->model('parcelas_os_model');
            $this->parcelas_os_model->saveParcelas($idOs, $parcelas);
            echo json_encode(['result' => true, 'message' => 'OS atualizada. Lançamento não criado.']);
            return;
        }

        $this->load->model('financeiro_model');
        $lancamentosIds = [];

        foreach ($parcelas as $parcela) {
            // Converter data de vencimento
            $dataVencimento = date('Y-m-d');
            if (!empty($parcela['data_vencimento'])) {
                if (strpos($parcela['data_vencimento'], '/') !== false) {
                    $dataParts = explode('/', $parcela['data_vencimento']);
                    if (count($dataParts) == 3) {
                        $dataVencimento = $dataParts[2] . '-' . $dataParts[1] . '-' . $dataParts[0];
                    }
                } else {
                    $dataVencimento = $parcela['data_vencimento'];
                }
            } elseif (!empty($parcela['dias']) && $parcela['dias'] > 0) {
                $dataVencimento = date('Y-m-d', strtotime('+' . intval($parcela['dias']) . ' days'));
            }

            $valor = floatval($parcela['valor']) ?: 0;
            if ($valor <= 0) {
                continue; // Pular parcelas sem valor
            }

            // Determinar status (se já está pago ou pendente)
            $statusPagamento = (isset($parcela['status']) && $parcela['status'] === 'pago') ? 'pago' : 'pendente';
            $valorPago = ($statusPagamento === 'pago') ? $valor : 0;
            $baixado = ($statusPagamento === 'pago') ? 1 : 0;
            // Se está pago, a data de pagamento é a data de vencimento
            $dataPagamento = ($statusPagamento === 'pago') ? $dataVencimento : null;

            $dataLancamento = [
                'descricao' => 'OS #' . $idOs . ' - Parcela ' . $parcela['numero'] . ' - ' . htmlspecialchars($os->nomeCliente),
                'valor' => $valor,
                'valor_desconto' => $valor,
                'valor_pago' => $valorPago,
                'status_pagamento' => $statusPagamento,
                'desconto' => 0,
                'tipo_desconto' => 'real',
                'data_vencimento' => $dataVencimento,
                'data_pagamento' => $dataPagamento,
                'baixado' => $baixado,
                'cliente_fornecedor' => $os->nomeCliente,
                'clientes_id' => $os->clientes_id,
                'forma_pgto' => $parcela['forma_pgto'] ?? '',
                'tipo' => 'receita',
                'observacoes' => (!empty($parcela['observacao']) ? $parcela['observacao'] . ' - ' : '') . 
                                'Referente à OS #' . $idOs,
                'usuarios_id' => $this->session->userdata('id_admin')
            ];
            
            // Adicionar conta bancária se fornecida
            if (isset($parcela['conta_id']) && !empty($parcela['conta_id'])) {
                $dataLancamento['contas_id'] = $parcela['conta_id'];
            }
            
            $this->financeiro_model->add('lancamentos', $dataLancamento);
            $lancamentosIds[] = $this->db->insert_id();
        }

        // Atualizar parcelas na tabela parcelas_os com dados finais
        $this->load->model('parcelas_os_model');
        $this->parcelas_os_model->saveParcelas($idOs, $parcelas);

        // Vincular primeiro lançamento à OS
        if (!empty($lancamentosIds)) {
            $this->os_model->edit('os', ['lancamento' => $lancamentosIds[0]], 'idOs', $idOs);
        }

        log_info('Gerou lançamento financeiro para OS #' . $idOs . ' usando parcelas. Lançamentos: ' . implode(', ', $lancamentosIds));

        $mensagem = count($lancamentosIds) . ' lançamento(s) criado(s) com sucesso!';
        echo json_encode(['result' => true, 'message' => $mensagem, 'lancamentos' => $lancamentosIds]);
    }

    /**
     * Gera lançamento financeiro a partir de uma OS (método antigo - mantido para compatibilidade)
     */
    public function gerarLancamento()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            echo json_encode(['result' => false, 'message' => 'Você não tem permissão']);
            return;
        }

        $idOs = $this->input->post('idOs');
        $formaPgto = $this->input->post('forma_pgto');
        $parcelas = intval($this->input->post('parcelas')) ?: 1;
        $entrada = floatval(str_replace(',', '.', $this->input->post('entrada'))) ?: 0;
        $dataVencimento = $this->input->post('data_vencimento');
        $criarLancamento = $this->input->post('criar_lancamento');

        if (!$idOs) {
            echo json_encode(['result' => false, 'message' => 'ID da OS inválido']);
            return;
        }

        // Buscar OS
        $os = $this->os_model->getById($idOs);
        if (!$os) {
            echo json_encode(['result' => false, 'message' => 'OS não encontrada']);
            return;
        }

        // Verificar se já tem lançamento vinculado
        if (!empty($os->lancamento)) {
            echo json_encode(['result' => false, 'message' => 'Esta OS já possui um lançamento financeiro vinculado']);
            return;
        }

        // Calcular valor total
        $produtos = $this->os_model->getProdutos($idOs);
        $servicos = $this->os_model->getServicos($idOs);
        
        $totalProdutos = 0;
        $totalServicos = 0;
        
        foreach ($produtos as $p) {
            $totalProdutos += $p->preco * $p->quantidade;
        }
        foreach ($servicos as $s) {
            $totalServicos += $s->preco * $s->quantidade;
        }
        
        $valorTotal = $totalProdutos + $totalServicos;
        
        if ($valorTotal <= 0) {
            echo json_encode(['result' => false, 'message' => 'Valor total da OS é zero']);
            return;
        }

        // Converter data de vencimento
        if ($dataVencimento) {
            $dataParts = explode('/', $dataVencimento);
            if (count($dataParts) == 3) {
                $dataVencimento = $dataParts[2] . '-' . $dataParts[1] . '-' . $dataParts[0];
            } else {
                $dataVencimento = date('Y-m-d');
            }
        } else {
            $dataVencimento = date('Y-m-d');
        }

        // Atualizar OS com dados de pagamento
        $dataOs = [
            'forma_pgto' => $formaPgto,
            'parcelas' => $parcelas,
            'valor_entrada' => $entrada,
            'faturado' => 1
        ];
        $this->os_model->edit('os', $dataOs, 'idOs', $idOs);

        // Se não deve criar lançamento, apenas retornar sucesso
        if (!$criarLancamento || $criarLancamento == '0') {
            echo json_encode(['result' => true, 'message' => 'OS atualizada. Lançamento não criado.']);
            return;
        }

        $this->load->model('financeiro_model');
        $lancamentosIds = [];

        // Se tem entrada, criar lançamento de entrada como pago
        if ($entrada > 0) {
            $dataEntrada = [
                'descricao' => 'Entrada - OS #' . $idOs . ' - ' . htmlspecialchars($os->nomeCliente),
                'valor' => $entrada,
                'valor_desconto' => $entrada,
                'valor_pago' => $entrada,
                'status_pagamento' => 'pago',
                'desconto' => 0,
                'tipo_desconto' => 'real',
                'data_vencimento' => date('Y-m-d'),
                'data_pagamento' => date('Y-m-d'),
                'baixado' => 1,
                'cliente_fornecedor' => $os->nomeCliente,
                'clientes_id' => $os->clientes_id,
                'forma_pgto' => $formaPgto,
                'tipo' => 'receita',
                'observacoes' => 'Entrada referente à OS #' . $idOs,
                'usuarios_id' => $this->session->userdata('id_admin')
            ];
            
            $this->financeiro_model->add('lancamentos', $dataEntrada);
            $lancamentosIds[] = $this->db->insert_id();
        }

        // Calcular valor restante após entrada
        $valorRestante = $valorTotal - $entrada;

        if ($valorRestante > 0) {
            if ($parcelas > 1) {
                // Criar parcelas
                $valorParcela = $valorRestante / $parcelas;
                
                for ($i = 1; $i <= $parcelas; $i++) {
                    $dataVencimentoParcela = date('Y-m-d', strtotime($dataVencimento . ' + ' . ($i - 1) . ' months'));
                    
                    $dataParcela = [
                        'descricao' => 'OS #' . $idOs . ' - Parcela ' . $i . '/' . $parcelas . ' - ' . htmlspecialchars($os->nomeCliente),
                        'valor' => round($valorParcela, 2),
                        'valor_desconto' => round($valorParcela, 2),
                        'valor_pago' => 0,
                        'status_pagamento' => 'pendente',
                        'desconto' => 0,
                        'tipo_desconto' => 'real',
                        'data_vencimento' => $dataVencimentoParcela,
                        'data_pagamento' => null,
                        'baixado' => 0,
                        'cliente_fornecedor' => $os->nomeCliente,
                        'clientes_id' => $os->clientes_id,
                        'forma_pgto' => $formaPgto,
                        'tipo' => 'receita',
                        'observacoes' => 'Parcela ' . $i . '/' . $parcelas . ' referente à OS #' . $idOs,
                        'usuarios_id' => $this->session->userdata('id_admin')
                    ];
                    
                    $this->financeiro_model->add('lancamentos', $dataParcela);
                    $lancamentosIds[] = $this->db->insert_id();
                }
            } else {
                // Lançamento único
                $dataLancamento = [
                    'descricao' => 'OS #' . $idOs . ' - ' . htmlspecialchars($os->nomeCliente),
                    'valor' => $valorRestante,
                    'valor_desconto' => $valorRestante,
                    'valor_pago' => 0,
                    'status_pagamento' => 'pendente',
                    'desconto' => 0,
                    'tipo_desconto' => 'real',
                    'data_vencimento' => $dataVencimento,
                    'data_pagamento' => null,
                    'baixado' => 0,
                    'cliente_fornecedor' => $os->nomeCliente,
                    'clientes_id' => $os->clientes_id,
                    'forma_pgto' => $formaPgto,
                    'tipo' => 'receita',
                    'observacoes' => 'Referente à OS #' . $idOs,
                    'usuarios_id' => $this->session->userdata('id_admin')
                ];
                
                $this->financeiro_model->add('lancamentos', $dataLancamento);
                $lancamentosIds[] = $this->db->insert_id();
            }
        }

        // Vincular primeiro lançamento à OS
        if (!empty($lancamentosIds)) {
            $this->os_model->edit('os', ['lancamento' => $lancamentosIds[0]], 'idOs', $idOs);
        }

        log_info('Gerou lançamento financeiro para OS #' . $idOs . '. Lançamentos: ' . implode(', ', $lancamentosIds));

        $mensagem = 'Lançamento(s) criado(s) com sucesso!';
        if ($entrada > 0) {
            $mensagem .= ' Entrada: R$ ' . number_format($entrada, 2, ',', '.');
        }
        if ($parcelas > 1) {
            $mensagem .= ' ' . $parcelas . ' parcelas de R$ ' . number_format($valorRestante / $parcelas, 2, ',', '.');
        }

        echo json_encode(['result' => true, 'message' => $mensagem, 'lancamentos' => $lancamentosIds]);
    }

    /**
     * Gera lançamento financeiro automaticamente usando dados já salvos na OS
     * Chamado quando a OS muda para Finalizado ou Faturado
     */
    private function gerarLancamentoAutomatico($idOs)
    {
        // Buscar OS
        $os = $this->os_model->getById($idOs);
        if (!$os) {
            log_info("Erro ao gerar lançamento automático: OS #{$idOs} não encontrada");
            return false;
        }

        // Verificar se já tem lançamento
        if (!empty($os->lancamento)) {
            log_info("OS #{$idOs} já possui lançamento vinculado. Pulando geração automática.");
            return false;
        }

        // Verificar se tem parcelas configuradas ou forma de pagamento (compatibilidade)
        $this->load->model('parcelas_os_model');
        $parcelasConfiguradas = $this->parcelas_os_model->getByOs($idOs);
        
        if (empty($parcelasConfiguradas) && empty($os->forma_pgto)) {
            log_info("OS #{$idOs} não possui parcelas ou forma de pagamento configurada. Pulando geração automática.");
            return false;
        }

        // Calcular valor total
        $produtos = $this->os_model->getProdutos($idOs);
        $servicos = $this->os_model->getServicos($idOs);
        
        $totalProdutos = 0;
        $totalServicos = 0;
        
        foreach ($produtos as $p) {
            $totalProdutos += $p->preco * $p->quantidade;
        }
        foreach ($servicos as $s) {
            $totalServicos += $s->preco * $s->quantidade;
        }
        
        $valorTotal = $totalProdutos + $totalServicos;
        
        if ($valorTotal <= 0) {
            log_info("OS #{$idOs} tem valor total zero. Pulando geração automática.");
            return false;
        }

        // Buscar parcelas configuradas
        $this->load->model('parcelas_os_model');
        $parcelasConfiguradas = $this->parcelas_os_model->getByOs($idOs);
        
        $this->load->model('financeiro_model');
        $lancamentosIds = [];
        
        // Se tem parcelas configuradas, usar elas
        if (!empty($parcelasConfiguradas) && count($parcelasConfiguradas) > 0) {
            foreach ($parcelasConfiguradas as $parcela) {
                $valor = floatval($parcela->valor) ?: 0;
                if ($valor <= 0) {
                    continue; // Pular parcelas sem valor
                }
                
                // Usar data de vencimento da parcela ou calcular baseado em dias
                $dataVencimento = date('Y-m-d');
                if (!empty($parcela->data_vencimento)) {
                    $dataVencimento = $parcela->data_vencimento;
                } elseif (!empty($parcela->dias) && $parcela->dias > 0) {
                    $dataVencimento = date('Y-m-d', strtotime('+' . intval($parcela->dias) . ' days'));
                }
                
                // Determinar status
                $statusPagamento = ($parcela->status === 'pago') ? 'pago' : 'pendente';
                $valorPago = ($statusPagamento === 'pago') ? $valor : 0;
                $baixado = ($statusPagamento === 'pago') ? 1 : 0;
                $dataPagamento = ($statusPagamento === 'pago') ? date('Y-m-d') : null;
                
                $dataLancamento = [
                    'descricao' => 'OS #' . $idOs . ' - Parcela ' . $parcela->numero . ' - ' . htmlspecialchars($os->nomeCliente),
                    'valor' => $valor,
                    'valor_desconto' => $valor,
                    'valor_pago' => $valorPago,
                    'status_pagamento' => $statusPagamento,
                    'desconto' => 0,
                    'tipo_desconto' => 'real',
                    'data_vencimento' => $dataVencimento,
                    'data_pagamento' => $dataPagamento,
                    'baixado' => $baixado,
                    'cliente_fornecedor' => $os->nomeCliente,
                    'clientes_id' => $os->clientes_id,
                    'forma_pgto' => $parcela->forma_pgto ?? '',
                    'tipo' => 'receita',
                    'observacoes' => (!empty($parcela->observacao) ? $parcela->observacao . ' - ' : '') . 
                                    (!empty($parcela->detalhes) ? 'Detalhes: ' . $parcela->detalhes . ' - ' : '') . 
                                    'Referente à OS #' . $idOs,
                    'usuarios_id' => $this->session->userdata('id_admin')
                ];
                
                // Adicionar conta bancária se fornecida
                if (isset($parcela->conta_id) && !empty($parcela->conta_id)) {
                    $dataLancamento['contas_id'] = $parcela->conta_id;
                }
                
                $this->financeiro_model->add('lancamentos', $dataLancamento);
                $lancamentosIds[] = $this->db->insert_id();
            }
        } else {
            // Se não tem parcelas configuradas, criar lançamento único (compatibilidade)
            $dataLancamento = [
                'descricao' => 'OS #' . $idOs . ' - ' . htmlspecialchars($os->nomeCliente),
                'valor' => $valorTotal,
                'valor_desconto' => $valorTotal,
                'valor_pago' => 0,
                'status_pagamento' => 'pendente',
                'desconto' => 0,
                'tipo_desconto' => 'real',
                'data_vencimento' => date('Y-m-d'),
                'data_pagamento' => null,
                'baixado' => 0,
                'cliente_fornecedor' => $os->nomeCliente,
                'clientes_id' => $os->clientes_id,
                'forma_pgto' => $os->forma_pgto ?? '',
                'tipo' => 'receita',
                'observacoes' => 'Referente à OS #' . $idOs,
                'usuarios_id' => $this->session->userdata('id_admin')
            ];
            
            $this->financeiro_model->add('lancamentos', $dataLancamento);
            $lancamentosIds[] = $this->db->insert_id();
        }

        // Vincular primeiro lançamento à OS
        if (!empty($lancamentosIds)) {
            $this->os_model->edit('os', ['lancamento' => $lancamentosIds[0]], 'idOs', $idOs);
        }

        log_info('Gerou lançamento financeiro automático para OS #' . $idOs . '. Lançamentos: ' . implode(', ', $lancamentosIds));
        return true;
    }

    /**
     * Retorna dados da OS para o modal de faturamento
     */
    /**
     * Retorna dados da OS para o modal de faturamento
     * Inclui parcelas se existirem
     */
    public function getDadosOsJson($idOs)
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            echo json_encode(['result' => false, 'message' => 'Sem permissão']);
            return;
        }

        $os = $this->os_model->getById($idOs);
        if (!$os) {
            echo json_encode(['result' => false, 'message' => 'OS não encontrada']);
            return;
        }

        $produtos = $this->os_model->getProdutos($idOs);
        $servicos = $this->os_model->getServicos($idOs);
        
        $totalProdutos = 0;
        $totalServicos = 0;
        
        foreach ($produtos as $p) {
            $totalProdutos += $p->preco * $p->quantidade;
        }
        foreach ($servicos as $s) {
            $totalServicos += $s->preco * $s->quantidade;
        }
        
        $valorTotal = $totalProdutos + $totalServicos;

        // Buscar parcelas da OS
        $this->load->model('parcelas_os_model');
        $parcelas = $this->parcelas_os_model->getByOs($idOs);
        
        // Preparar parcelas para JSON
        $parcelasArray = [];
        foreach ($parcelas as $p) {
            $parcelasArray[] = [
                'id' => $p->idParcela,
                'numero' => intval($p->numero),
                'dias' => intval($p->dias),
                'valor' => floatval($p->valor),
                'observacao' => $p->observacao ?? '',
                'data_vencimento' => $p->data_vencimento,
                'forma_pgto' => $p->forma_pgto ?? '',
                'detalhes' => $p->detalhes ?? '',
                'status' => $p->status ?? 'pendente'
            ];
        }

        echo json_encode([
            'result' => true,
            'os' => $os,
            'valorTotal' => $valorTotal,
            'temLancamento' => !empty($os->lancamento),
            'parcelas' => $parcelasArray
        ]);
    }
}
