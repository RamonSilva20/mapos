<?php

namespace Libraries\Api;

use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Roteamento\Roteador;

final class Rotas
{
    public const HELPERS = ['usuarios', 'manutencao', 'os', 'vendas', 'painel', 'clientes', 'produtos', 'financeiro', 'configuracoes', 'armazenamento', 'relatorios', 'conecte'];

    /** @var list<class-string<Modulo>> */
    public const MODULOS = [
        Modulos\Autenticacao::class,
        Modulos\Conta::class,
        Modulos\VisaoGeral::class,
        Modulos\Clientes::class,
        Modulos\Ordens::class,
        Modulos\Vendas::class,
        Modulos\Produtos::class,
        Modulos\Servicos::class,
        Modulos\Garantias::class,
        Modulos\Arquivos::class,
        Modulos\Lancamentos::class,
        Modulos\Cobrancas::class,
        Modulos\Relatorios::class,
        Modulos\Usuarios::class,
        Modulos\Permissoes::class,
        Modulos\Administracao::class,
        Modulos\AreaDoCliente::class,
    ];

    public const TAGS = [
        'Autenticação' => 'Login, segundo fator, renovação de tokens e sessões dos usuários do painel.',
        'Minha conta' => 'Dados, senha, tema, foto e autenticação em dois fatores da conta logada.',
        'Painel' => 'Números do painel e calendário de OS.',
        'Busca' => 'Busca geral e sugestões para os campos de OS e vendas.',
        'Clientes' => 'Clientes e fornecedores.',
        'Ordens de serviço' => 'OS: dados, produtos, serviços, descontos, faturamento, anexos, anotações e histórico.',
        'Vendas' => 'Vendas de produtos: itens, descontos, faturamento e PIX.',
        'Produtos' => 'Cadastro de produtos e ajuste de estoque.',
        'Serviços' => 'Cadastro de serviços.',
        'Termos de garantia' => 'Textos de garantia usados nas OS.',
        'Arquivos' => 'Documentos guardados no sistema.',
        'Cobranças' => 'Boletos, PIX e links de pagamento gerados nos gateways (Asaas, Efí, Mercado Pago).',
        'Lançamentos' => 'Receitas e despesas: listagem com totais, parcelamento, baixa e visão geral.',
        'Relatórios' => 'Relatórios em JSON, PDF ou planilha.',
        'Usuários' => 'Usuários do painel.',
        'Grupos de permissão' => 'Grupos e o catálogo de permissões.',
        'Emitente' => 'Dados da empresa usados nas impressões, e-mails e PIX.',
        'Configurações' => 'Preferências do sistema, e-mail, gateways de pagamento e API.',
        'Auditoria' => 'Registro das ações feitas no sistema.',
        'Fila de e-mails' => 'E-mails aguardando envio, enviados e com falha.',
        'Sistema' => 'Versão, atualização do banco e do Map-OS e backup.',
        'Cliente: autenticação' => 'Login, cadastro, recuperação de senha e sessões dos clientes. Os tokens de cliente só valem nas rotas /cliente.',
        'Cliente: conta' => 'Resumo e dados do cliente logado.',
        'Cliente: ordens de serviço' => 'OS do cliente: consulta, abertura, anexos e PIX.',
        'Cliente: compras' => 'Compras do cliente e PIX.',
        'Cliente: cobranças' => 'Cobranças geradas para o cliente.',
    ];

    public static function roteador(): Roteador
    {
        return new Roteador(self::todas());
    }

    /** @return list<Rota> */
    public static function todas(): array
    {
        $rotas = [];
        foreach (self::MODULOS as $modulo) {
            array_push($rotas, ...$modulo::rotas());
        }

        return $rotas;
    }
}
