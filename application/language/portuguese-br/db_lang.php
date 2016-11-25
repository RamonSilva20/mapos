<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Não foi possível determinar as definições de banco de dados usando a string de conexão fornecida.';
$lang['db_unable_to_connect'] = 'Não foi possível conectar ao banco de dados usando as definições fornecidas.';
$lang['db_unable_to_select'] = 'Não foi possível selecionar o banco: %s';
$lang['db_unable_to_create'] = 'Não foi possível criar o banco: %s';
$lang['db_invalid_query'] = 'A query fornecida é inválida.';
$lang['db_must_set_table'] = 'Você deve informar a tabela que será usada em sua pesquisa.';
$lang['db_must_use_set'] = 'Você deve usar o método "set" para atualizar um registro.';
$lang['db_must_use_index'] = 'Você deve especificar um índice para casar em atualizações em lote.';
$lang['db_batch_missing_index'] = 'Uma ou mais linhas enviadas para atualização em lote não têm um índice especificado.';
$lang['db_must_use_where'] = 'Atualizações não são permitidas sem o uso da cláusula "where".';
$lang['db_del_must_use_where'] = 'O comando "delete" não é permitido sem o uso de uma cláusula "where" ou "like".';
$lang['db_field_param_missing'] = 'Para buscar campos é necessário informar o nome da tabela como um parâmetro.';
$lang['db_unsupported_function'] = 'Este recurso não está disponível para o banco de dados em uso.';
$lang['db_transaction_failure'] = 'Falha na transação: Foi feito o rollback.';
$lang['db_unable_to_drop'] = 'Não foi possível remover o banco especificado.';
$lang['db_unsupported_feature'] = 'Este recurso não é suportado pelo banco de dados em uso.';
$lang['db_unsupported_compression'] = 'O formato de compressão de arquivos não é suportado pelo servidor.';
$lang['db_filepath_error'] = 'Não foi possível escrever no caminho informado.';
$lang['db_invalid_cache_path'] = 'O caminho de cache informado não é válido ou não tem permissão de escrita.';
$lang['db_table_name_required'] = 'Um nome de tabela é requisito para esta operação.';
$lang['db_column_name_required'] = 'Um nome de coluna é requisito para esta operação.';
$lang['db_column_definition_required'] = 'A definição de uma coluna é requisito para esta operação.';
$lang['db_unable_to_set_charset'] = 'Não foi possível definir o conjunto de caracteres: %s';
$lang['db_error_heading'] = 'Ocorreu um erro de banco de dados';
