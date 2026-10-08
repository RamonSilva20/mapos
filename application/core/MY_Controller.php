<?php

class MY_Controller extends CI_Controller
{
    public $data = [
        'configuration' => [
            // Itens por página das listagens. O markup da paginação legada
            // (create_links) fica em application/config/pagination.php; as
            // telas novas usam o componente pagination via paginacao().
            'per_page' => 10,
            'app_name' => 'Map-OS',
            'app_theme' => 'default',
            'os_notification' => 'cliente',
            'control_estoque' => '1',
            'notifica_whats' => '',
            'control_baixa' => '0',
            'control_editos' => '1',
            'control_datatable' => '1',
            'pix_key' => '',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        if ((! session_id()) || (! $this->session->userdata('logado'))) {
            redirect('login');
        }
        $this->load_configuration();
        $this->verificarPermissaoDaRota();
    }

    /**
     * Confere a rota atual contra application/config/permissions_map.php.
     *
     * Roda antes do método do controller. Método público fora do mapa é
     * negado, para que esquecer a checagem numa rota nova feche o acesso em
     * vez de abri-lo.
     */
    protected function verificarPermissaoDaRota()
    {
        $controller = $this->router->class;
        $metodo = $this->router->method;

        // Rota que o CodeIgniter vai responder com 404 continua com 404.
        if (! $this->ehRota($metodo)) {
            return;
        }

        $this->config->load('permissions_map');
        $regra = $this->regraDaRota((array) $this->config->item('permissions_map'), $controller, $metodo);

        if (! $this->regraPermite($regra)) {
            $this->negarAcesso();
        }
    }

    /**
     * Diz se o método é alcançável pela URL, com os mesmos critérios do
     * CodeIgniter: público, sem prefixo "_" e não herdado de CI_Controller.
     */
    protected function ehRota($metodo)
    {
        $metodo = (string) $metodo;

        if ($metodo === '' || $metodo[0] === '_' || ! method_exists($this, $metodo)) {
            return false;
        }

        if (method_exists('CI_Controller', $metodo)) {
            return false;
        }

        return (new ReflectionMethod($this, $metodo))->isPublic();
    }

    /**
     * Busca a regra do método no mapa, ignorando maiúsculas e minúsculas.
     *
     * @return string|array|false|null null quando a rota não está no mapa
     */
    protected function regraDaRota(array $mapa, $controller, $metodo)
    {
        foreach ($mapa as $nomeController => $metodos) {
            if (strcasecmp($nomeController, (string) $controller) !== 0) {
                continue;
            }

            foreach ($metodos as $nomeMetodo => $regra) {
                if (strcasecmp($nomeMetodo, (string) $metodo) === 0) {
                    return $regra;
                }
            }
        }

        return null;
    }

    /**
     * Aplica uma regra do mapa ao usuário logado. Ver o formato no cabeçalho
     * de permissions_map.php.
     */
    protected function regraPermite($regra)
    {
        if ($regra === '*') {
            return true;
        }

        if (is_string($regra) && $regra !== '') {
            return $this->hasAnyPermission([$regra]);
        }

        if (is_array($regra) && isset($regra['todas'])) {
            if (! is_array($regra['todas']) || $regra['todas'] === []) {
                return false;
            }

            foreach ($regra['todas'] as $permissao) {
                if (! $this->hasAnyPermission([$permissao])) {
                    return false;
                }
            }

            return true;
        }

        if (is_array($regra) && $regra !== []) {
            return $this->hasAnyPermission(array_values($regra));
        }

        // null (fora do mapa), false (não é rota) e qualquer valor malformado
        return false;
    }

    /**
     * Responde a uma rota negada: JSON 403 para AJAX, aviso e redirecionamento
     * para o painel nas demais.
     */
    protected function negarAcesso()
    {
        $mensagem = 'Você não tem permissão para acessar esta página.';

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(['result' => false, 'message' => $mensagem]));
            $this->output->_display();
            $this->encerrar();

            return;
        }

        $this->session->set_flashdata('error', $mensagem);
        $this->redirecionarParaOPainel();
    }

    protected function redirecionarParaOPainel()
    {
        redirect(base_url());
    }

    protected function encerrar()
    {
        exit;
    }

    private function load_configuration()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $configuracoes = $this->CI->db->get('configuracoes')->result();

        foreach ($configuracoes as $c) {
            $this->data['configuration'][$c->config] = $c->valor;
        }
    }

    /**
     * Verifica se o usuário logado possui ao menos uma das permissões informadas.
     */
    protected function hasAnyPermission(array $permissoes)
    {
        foreach ($permissoes as $permissao) {
            if ($this->permission->checkPermission($this->session->userdata('permissao'), $permissao)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Props do componente pagination para uma listagem nova.
     *
     *     $this->data['paginacao'] = $this->paginacao(site_url('clientes/gerenciar'), $total, $this->uri->segment(3));
     *     // na view: <?= component('pagination', $paginacao) ?>
     *
     * O offset na URL é o mesmo do CI_Pagination, então o model continua
     * recebendo (per_page, offset) como hoje. Com $parametroDaQuery, o offset
     * vai na query string (ex. 'per_page', como no financeiro).
     *
     * @param  string      $urlBase           URL da listagem, sem o offset
     * @param  int         $totalLinhas       Total de registros
     * @param  mixed       $offset            Offset atual (segmento da URL ou query)
     * @param  string|null $parametroDaQuery  Nome do parâmetro quando o offset vai na query
     */
    protected function paginacao($urlBase, $totalLinhas, $offset, $parametroDaQuery = null)
    {
        return paginacaoProps([
            'base_url' => (string) $urlBase,
            'total_rows' => (int) $totalLinhas,
            'per_page' => (int) $this->data['configuration']['per_page'],
            'offset' => $offset,
            'query_string' => $parametroDaQuery,
        ]);
    }

    public function layout()
    {
        // load views
        $this->load->view('tema/topo', $this->data);
        $this->load->view('tema/menu');
        $this->load->view('tema/conteudo');
        $this->load->view('tema/rodape');
    }
}
