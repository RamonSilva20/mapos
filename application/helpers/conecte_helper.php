<?php

/**
 * Regras da área do cliente (#2848), fora do controller Mine para serem
 * testadas: menu, garantias, o link da OS enviado por e-mail e os dados que
 * o cliente pode gravar no próprio cadastro.
 */
if (! function_exists('conecteMenu')) {
    /**
     * Itens da navegação da área do cliente. Os marcados com tabbar formam a
     * tabbar-mobile do DESIGN.md (Início, Minhas OS, Compras, Conta); a Top
     * Nav do desktop mostra também as cobranças, que no celular ficam no
     * início e na conta.
     *
     * @return list<array{chave: string, label: string, curto: string, icon: string, url: string, tabbar: bool, atual: bool}>
     */
    function conecteMenu(string $atual): array
    {
        $itens = [
            ['painel', 'Início', 'Início', 'house', 'mine/painel', true],
            ['os', 'Ordens de serviço', 'Minhas OS', 'wrench', 'mine/os', true],
            ['compras', 'Compras', 'Compras', 'shopping-cart', 'mine/compras', true],
            ['cobrancas', 'Cobranças', 'Cobranças', 'receipt', 'mine/cobrancas', false],
            ['conta', 'Minha conta', 'Conta', 'circle-user', 'mine/conta', true],
        ];

        return array_map(static fn (array $item) => [
            'chave' => $item[0],
            'label' => $item[1],
            'curto' => $item[2],
            'icon' => $item[3],
            'url' => $item[4],
            'tabbar' => $item[5],
            'atual' => $item[0] === $atual,
        ], $itens);
    }
}

if (! function_exists('conecteGarantiaOsPill')) {
    /**
     * Pill da garantia de uma OS: só existe depois de finalizada ou faturada
     * (osVencimentoGarantia()); vigente em success, vencida em neutral.
     *
     * @return array{label: string, variant: string}|null
     */
    function conecteGarantiaOsPill(object $os, ?string $hoje = null): ?array
    {
        return vendaGarantiaPill(osVencimentoGarantia($os->dataFinal ?? null, $os->garantia ?? null, $os->status ?? null), $hoje);
    }
}

if (! function_exists('conecteIdDoLinkDoEmail')) {
    /**
     * Id da OS a partir do número do link do e-mail (y = 7653 × id + 44023).
     * É só uma ofuscação reversível, não um segredo: quem abre o link ainda
     * precisa estar logado e ser o dono da OS. Valor fora da conta, null.
     */
    function conecteIdDoLinkDoEmail($y): ?int
    {
        if (! is_scalar($y) || ! ctype_digit((string) $y)) {
            return null;
        }

        $resto = (int) $y - 44023;

        return $resto > 0 && $resto % 7653 === 0 ? intdiv($resto, 7653) : null;
    }
}

if (! function_exists('conecteDadosDoCliente')) {
    /**
     * Dados que o próprio cliente grava (cadastro e "Meus dados"), a partir
     * do POST já validado. Usa as regras do formulário do painel
     * (clienteDadosDoFormulario()), mas o cliente nunca decide se é
     * fornecedor: no cadastro entra como cliente e, na edição, o campo não
     * muda.
     *
     * @param  mixed  $post  $this->input->post()
     */
    function conecteDadosDoCliente($post, bool $novo): array
    {
        $dados = clienteDadosDoFormulario(is_array($post) ? array_diff_key($post, ['fornecedor' => true]) : [], $novo);
        unset($dados['fornecedor']);

        return $novo ? $dados + ['fornecedor' => 0] : $dados;
    }
}

if (! function_exists('conecteRegrasDoCliente')) {
    /**
     * Regras do form_validation do cadastro e de "Meus dados". O e-mail é o
     * login, então é obrigatório e único; o documento, quando informado, é
     * válido e único. $idCliente exclui o próprio cadastro das checagens de
     * único (null no cadastro novo).
     *
     * A senha é obrigatória no cadastro; na edição, só quando o cliente quer
     * trocar, e a confirmação precisa bater.
     *
     * @return list<array{field: string, label: string, rules: string, errors?: array<string, string>}>
     */
    function conecteRegrasDoCliente(?int $idCliente): array
    {
        $id = $idCliente === null ? '0' : (string) $idCliente;
        $senha = $idCliente === null ? 'required|min_length[8]|max_length[72]' : 'min_length[8]|max_length[72]';

        return [
            ['field' => 'nomeCliente', 'label' => 'Nome', 'rules' => 'required|trim|max_length[255]'],
            ['field' => 'documento', 'label' => 'CPF ou CNPJ', 'rules' => 'trim|verific_cpf_cnpj|unique[clientes.documento.' . $id . '.idClientes]', 'errors' => [
                'verific_cpf_cnpj' => 'Informe um CPF ou CNPJ válido.',
                'unique' => $idCliente === null ? 'Este CPF ou CNPJ já tem cadastro. Entre com o e-mail dele ou recupere a senha.' : 'Este CPF ou CNPJ não pode ser usado. Confira o número ou fale com a assistência.',
            ]],
            ['field' => 'email', 'label' => 'E-mail', 'rules' => 'required|trim|valid_email|max_length[100]|unique[clientes.email.' . $id . '.idClientes]', 'errors' => [
                'unique' => $idCliente === null ? 'Este e-mail já tem cadastro. Entre com ele ou recupere a senha.' : 'Este e-mail não pode ser usado. Informe outro.',
            ]],
            ['field' => 'telefone', 'label' => 'Telefone', 'rules' => 'trim|max_length[20]'],
            ['field' => 'celular', 'label' => 'Celular', 'rules' => 'trim|max_length[20]'],
            ['field' => 'contato', 'label' => 'Pessoa de contato', 'rules' => 'trim|max_length[45]'],
            ['field' => 'cep', 'label' => 'CEP', 'rules' => 'trim|max_length[9]'],
            ['field' => 'rua', 'label' => 'Rua', 'rules' => 'trim|max_length[70]'],
            ['field' => 'numero', 'label' => 'Número', 'rules' => 'trim|max_length[15]'],
            ['field' => 'complemento', 'label' => 'Complemento', 'rules' => 'trim|max_length[45]'],
            ['field' => 'bairro', 'label' => 'Bairro', 'rules' => 'trim|max_length[45]'],
            ['field' => 'cidade', 'label' => 'Cidade', 'rules' => 'trim|max_length[45]'],
            ['field' => 'estado', 'label' => 'UF', 'rules' => 'trim|max_length[2]'],
            ['field' => 'senha', 'label' => 'Senha', 'rules' => $senha],
        ];
    }
}

if (! function_exists('conecteErroDaConfirmacao')) {
    /**
     * Erro do campo senha_confirmacao, ou null. Fica fora das regras do
     * form_validation porque ele pula campos vazios: uma senha nova com a
     * confirmação em branco passaria.
     *
     * @param  mixed  $post
     */
    function conecteErroDaConfirmacao($post): ?string
    {
        $senha = is_array($post) && is_string($post['senha'] ?? null) ? $post['senha'] : '';
        $confirmacao = is_array($post) && is_string($post['senha_confirmacao'] ?? null) ? $post['senha_confirmacao'] : '';

        if ($senha === '' || hash_equals($senha, $confirmacao)) {
            return null;
        }

        return $confirmacao === '' ? 'Repita a senha para confirmar.' : 'A confirmação não é igual à senha.';
    }
}

if (! function_exists('conecteColunasOs')) {
    /**
     * Colunas do data-table das OS do cliente (início e listagem). A completa
     * mostra também a previsão, a garantia e o botão de imprimir.
     */
    function conecteColunasOs(bool $completa): array
    {
        $colunas = [
            ['label' => 'OS', 'nowrap' => true, 'render' => fn ($o) => component('link', ['label' => 'OS #' . (int) $o->idOs, 'href' => site_url('mine/visualizarOs/' . (int) $o->idOs), 'class' => 'font-medium'])],
            ['label' => 'Equipamento', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'render' => fn ($o) => osTextoCurto($o->descricaoProduto) ?: '—'],
            ['label' => 'Entrada', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => dataBr($o->dataInicial)],
        ];

        if ($completa) {
            $colunas[] = ['label' => 'Previsão', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($o) => dataBr($o->dataFinal)];
            $colunas[] = ['label' => 'Garantia', 'nowrap' => true, 'hide_until' => 'xl', 'render' => function ($o) {
                $pill = conecteGarantiaOsPill($o);

                return $pill === null ? '—' : component('pill-status', $pill);
            }];
        }

        $colunas[] = ['label' => 'Status', 'nowrap' => true, 'render' => fn ($o) => component('pill-status', osStatusPill($o->status))];
        $colunas[] = ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($o) use ($completa) {
            $acoes = [component('button', ['label' => 'Ver OS #' . (int) $o->idOs, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine/visualizarOs/' . (int) $o->idOs)])];
            if ($completa) {
                $acoes[] = component('button', ['label' => 'Imprimir OS #' . (int) $o->idOs, 'icon' => 'printer', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine/imprimirOs/' . (int) $o->idOs), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
            }

            return $acoes;
        }];

        return $colunas;
    }
}

if (! function_exists('conecteColunasCompras')) {
    /** Colunas do data-table das compras do cliente (início e listagem). */
    function conecteColunasCompras(bool $completa): array
    {
        $colunas = [
            ['label' => 'Compra', 'nowrap' => true, 'render' => fn ($v) => component('link', ['label' => 'Venda #' . (int) $v->idVendas, 'href' => site_url('mine/visualizarCompra/' . (int) $v->idVendas), 'class' => 'font-medium'])],
            ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => dataBr($v->dataVenda)],
        ];

        if ($completa) {
            $colunas[] = ['label' => 'Vendedor', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($v) => (string) ($v->nome ?? '')];
        }

        $colunas[] = ['label' => 'Garantia', 'nowrap' => true, 'hide_until' => $completa ? 'md' : 'xl', 'render' => function ($v) {
            $pill = vendaGarantiaPill(vendaGarantiaAte($v->dataVenda, $v->garantia));

            return $pill === null ? '—' : component('pill-status', $pill);
        }];
        $colunas[] = ['label' => 'Status', 'nowrap' => true, 'render' => fn ($v) => component('pill-status', osStatusPill($v->status))];
        $colunas[] = ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($v) use ($completa) {
            $acoes = [component('button', ['label' => 'Ver venda #' . (int) $v->idVendas, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine/visualizarCompra/' . (int) $v->idVendas)])];
            if ($completa) {
                $acoes[] = component('button', ['label' => 'Imprimir venda #' . (int) $v->idVendas, 'icon' => 'printer', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine/imprimirCompra/' . (int) $v->idVendas), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
            }

            return $acoes;
        }];

        return $colunas;
    }
}

if (! function_exists('conecteReferenciaDaCobranca')) {
    /**
     * A que a cobrança se refere, com o link para a tela do cliente: OS ou
     * venda. As cobranças sem vínculo aparecem só com o número.
     *
     * @return array{label: string, href: string|null}
     */
    function conecteReferenciaDaCobranca(object $cobranca): array
    {
        if (! empty($cobranca->os_id)) {
            return ['label' => 'OS #' . (int) $cobranca->os_id, 'href' => site_url('mine/visualizarOs/' . (int) $cobranca->os_id)];
        }

        if (! empty($cobranca->vendas_id)) {
            return ['label' => 'Venda #' . (int) $cobranca->vendas_id, 'href' => site_url('mine/visualizarCompra/' . (int) $cobranca->vendas_id)];
        }

        return ['label' => 'Cobrança ' . (string) ($cobranca->charge_id ?: $cobranca->idCobranca), 'href' => null];
    }
}
