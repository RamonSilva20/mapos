<?php defined('BASEPATH') or exit('No direct script access allowed');

$config =

    array(
        'clientes' => array(
            array(
                'field' => 'nomeCliente',
                'label' => 'Nome',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'documento',
                'label' => 'CPF/CNPJ',
                'rules' => 'required|trim|verific_cpf_cnpj',
                'errors' => array(
                    'verific_cpf_cnpj' => "O campo %s não é um CPF ou CNPJ válido."
                ),

            ),
            array(
                'field' => 'telefone',
                'label' => 'Telefone',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|trim|valid_email',
            ),
            array(
                'field' => 'rua',
                'label' => 'Rua',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'numero',
                'label' => 'Número',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'bairro',
                'label' => 'Bairro',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'cidade',
                'label' => 'Cidade',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'estado',
                'label' => 'Estado',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'cep',
                'label' => 'CEP',
                'rules' => 'required|trim',
            )
        ),
        'servicos' => array(
            array(
                'field' => 'nome',
                'label' => 'Nome',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'descricao',
                'label' => '',
                'rules' => 'trim',
            ),
            array(
                'field' => 'preco',
                'label' => '',
                'rules' => 'required|trim',
            )
        ),
        'produtos' => array(
            array(
                'field' => 'descricao',
                'label' => '',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'unidade',
                'label' => 'Unidade',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'precoCompra',
                'label' => 'Preo de Compra',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'precoVenda',
                'label' => 'Preo de Venda',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'estoque',
                'label' => 'Estoque',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'estoqueMinimo',
                'label' => 'Estoque Mnimo',
                'rules' => 'trim',
            )
        ),
        'usuarios' => array(
            array(
                'field' => 'nome',
                'label' => 'Nome',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'rg',
                'label' => 'RG',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'cpf',
                'label' => 'CPF',
                'rules' => 'required|trim|verific_cpf_cnpj|is_unique[usuarios.cpf]',
                'errors' => array(
                    'verific_cpf_cnpj' => "O campo %s não é um CPF válido."
                ),
            ),
            array(
                'field' => 'rua',
                'label' => 'Rua',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'numero',
                'label' => 'Numero',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'bairro',
                'label' => 'Bairro',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'cidade',
                'label' => 'Cidade',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'estado',
                'label' => 'Estado',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'cep',
                'label' => 'CEP',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|trim|valid_email|is_unique[usuarios.email]',
            ),
            array(
                'field' => 'senha',
                'label' => 'Senha',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'telefone',
                'label' => 'Telefone',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'situacao',
                'label' => 'Situacao',
                'rules' => 'required|trim',
            )
        ),
        'os' => array(
            array(
                'field' => 'dataInicial',
                'label' => 'DataInicial',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'dataFinal',
                'label' => 'DataFinal',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'garantia',
                'label' => 'Garantia',
                'rules' => 'trim|numeric',
                'errors' => array(
                    'numeric' => 'Por favor digite apenas número.'
                ),
            ),
            array(
                'field' => 'termoGarantia',
                'label' => 'Termo Garantia',
                'rules' => 'trim',
            ),
            array(
                'field' => 'descricaoProduto',
                'label' => 'DescricaoProduto',
                'rules' => 'trim',
            ),
            array(
                'field' => 'defeito',
                'label' => 'Defeito',
                'rules' => 'trim',
            ),
            array(
                'field' => 'status',
                'label' => 'Status',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'observacoes',
                'label' => 'Observacoes',
                'rules' => 'trim',
            ),
            array(
                'field' => 'clientes_id',
                'label' => 'clientes',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'usuarios_id',
                'label' => 'usuarios_id',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'laudoTecnico',
                'label' => 'Laudo Tecnico',
                'rules' => 'trim',
            )
        ),
        'tiposUsuario' => array(
            array(
                'field' => 'nomeTipo',
                'label' => 'NomeTipo',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'situacao',
                'label' => 'Situacao',
                'rules' => 'required|trim',
            )
        ),
        'receita' => array(
            array(
                'field' => 'descricao',
                'label' => 'Descrição',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'valor',
                'label' => 'Valor',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'vencimento',
                'label' => 'Data Vencimento',
                'rules' => 'required|trim',
            ),

            array(
                'field' => 'cliente',
                'label' => 'Cliente',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'tipo',
                'label' => 'Tipo',
                'rules' => 'required|trim',
            )
        ),
        'despesa' => array(
            array(
                'field' => 'descricao',
                'label' => 'Descrição',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'valor',
                'label' => 'Valor',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'vencimento',
                'label' => 'Data Vencimento',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'fornecedor',
                'label' => 'Fornecedor',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'tipo',
                'label' => 'Tipo',
                'rules' => 'required|trim',
            )
        ),
        'garantias' => array(
            array(
                'field' => 'dataGarantia',
                'label' => 'dataGarantia',
                'rules' => 'trim',
            ),
            array(
                'field' => 'usuarios_id',
                'label' => 'usuarios_id',
                'rules' => 'trim',
            ),
            array(
                'field' => 'refGarantia',
                'label' => 'refGarantia',
                'rules' => 'trim',
            ),
            array(
                'field' => 'textoGarantia',
                'label' => 'textoGarantia',
                'rules' => 'required|trim',
            )
        ),
        'pagamentos' => array(
            array(
                'field' => 'Nome',
                'label' => 'nomePag',
                'rules' => 'trim',
            ),
            array(
                'field' => 'clientId',
                'label' => 'clientId',
                'rules' => 'trim',
            ),
            array(
                'field' => 'clientSecret',
                'label' => 'clientSecret',
                'rules' => 'trim',
            ),
            array(
                'field' => 'publicKey',
                'label' => 'publicKey',
                'rules' => 'trim',
            ),
            array(
                'field' => 'accessToken',
                'label' => 'accessToken',
                'rules' => 'trim',
            )
        ),
        'vendas' => array(
            array(

                'field' => 'dataVenda',
                'label' => 'Data da Venda',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'clientes_id',
                'label' => 'clientes',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'usuarios_id',
                'label' => 'usuarios_id',
                'rules' => 'trim|required',
            )
        ),
        'anotacoes_os' => array(
            array(
                'field' => 'anotacao',
                'label' => 'Anotação',
                'rules' => 'required|trim',
            ),
            array(
                'field' => 'os_id',
                'label' => 'ID Os',
                'rules' => 'trim|required|integer',
            )
        ),

    );


function verific_cpf_cnpj($cpfCnpjValor)
{

    $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpjValor);

    // Garante que o CNPJ é uma string
    $cpfCnpj = (string) $cpfCnpj;
    // Verifica CPF
    if (strlen($cpfCnpj) === 11) {
        return valid_cpf($cpfCnpj);
    }
    // Verifica CNPJ
    elseif (strlen($cpfCnpj) === 14) {
        return valid_cnpj($cpfCnpj);;
    }
    // Não retorna nada
    else {
        return false;
    }
}



/**
 * Verifica se o CPF informado é valido
 * @param     string
 * @return     bool
 * 
 */
function valid_cpf($cpf)
{
    // Verifiva se o número digitado contém todos os digitos
    // $cpf = str_pad(preg_replace('/[^0-9]/', '', $cpf), 11, '0', STR_PAD_LEFT);

    // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
    if (
        strlen($cpf) != 11 ||
        $cpf == '00000000000' ||
        $cpf == '11111111111' ||
        $cpf == '22222222222' ||
        $cpf == '33333333333' ||
        $cpf == '44444444444' ||
        $cpf == '55555555555' ||
        $cpf == '66666666666' ||
        $cpf == '77777777777' ||
        $cpf == '88888888888' ||
        $cpf == '99999999999'
    ) {
        return FALSE;
    } else {
        // Calcula os números para verificar se o CPF é verdadeiro
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{
                $c} * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;
            if ($cpf{
            $c} != $d) {
                return FALSE;
            }
        }
        return TRUE;
    }
}

// --------------------------------------------------------------------

/**
 * Valida CNPJ
 *
 * @author                  Luiz Otávio Miranda <contato@tutsup.com>
 * @access protected
 * @param  string     $cnpj
 * @return bool             true para CNPJ correto
 */

function valid_cnpj($cnpj)
{
    // Deixa o CNPJ com apenas números
    // $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    // Garante que o CNPJ é uma string
    //$cnpj = (string) $cnpj;

    // O valor original
    $cnpj_original = $cnpj;

    // Captura os primeiros 12 números do CNPJ
    $primeiros_numeros_cnpj = substr($cnpj, 0, 12);

    /**
     * Multiplicação do CNPJ
     *
     * @param string $cnpj Os digitos do CNPJ
     * @param int $posicoes A posição que vai iniciar a regressão
     * @return int O
     *
     */
    if (!function_exists('multiplica_cnpj')) {

        function multiplica_cnpj($cnpj, $posicao = 5)
        {
            // Variável para o cálculo
            $calculo = 0;

            // Laço para percorrer os item do cnpj
            for ($i = 0; $i < strlen($cnpj); $i++) {
                // Cálculo mais posição do CNPJ * a posição
                $calculo = $calculo + ($cnpj[$i] * $posicao);

                // Decrementa a posição a cada volta do laço
                $posicao--;

                // Se a posição for menor que 2, ela se torna 9
                if ($posicao < 2) {
                    $posicao = 9;
                }
            }
            // Retorna o cálculo
            return $calculo;
        }
    }

    // Faz o primeiro cálculo
    $primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);

    // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
    // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
    $primeiro_digito = ($primeiro_calculo % 11) < 2 ? 0 : 11 - ($primeiro_calculo % 11);

    // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
    // Agora temos 13 números aqui
    $primeiros_numeros_cnpj .= $primeiro_digito;

    // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
    $segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
    $segundo_digito = ($segundo_calculo % 11) < 2 ? 0 : 11 - ($segundo_calculo % 11);

    // Concatena o segundo dígito ao CNPJ
    $cnpj = $primeiros_numeros_cnpj . $segundo_digito;

    // Verifica se o CNPJ gerado é idêntico ao enviado
    if ($cnpj === $cnpj_original) {
        return true;
    } else {
        return false;
    }
}
