<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class OrdemUtils {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();

        $this->CI->load->model('os_model');
        $this->CI->load->model('vendas_model');
    }

    public function calculaOrdem($idOs)
    {
        if (!$idOs) {
            return null;
        }

        $this->CI->db->where('idOs', $idOs);
        $os = $this->CI->db->get('os')->row();
        $produtos = $this->CI->os_model->getProdutos($os->idOs);
        $servicos = $this->CI->os_model->getServicos($os->idOs);

        
        $totalProdutos = array_sum(array_map(fn($p) => $p->preco * $p->quantidade, $produtos));
        $totalServicos = array_sum(array_map(fn($s) => $s->preco * $s->quantidade, $servicos));
        

        $subtotal = $totalProdutos + $totalServicos;
    
        $valorDesconto = 0;
        if ($os->tipo_desconto == 'real') {
            $valorDesconto = $os->desconto;
        } elseif ($os->tipo_desconto == 'porcento') {
            $valorDesconto = $subtotal * ($os->desconto / 100);
        }
    
        $valorTotal = max(0, $subtotal - $valorDesconto);
    
        $descontoFormatado = $os->tipo_desconto == 'real' 
            ? 'R$ ' . number_format($os->desconto, 2, ',', '.') 
            : $os->desconto . ' %';
    
        return [
            'totalProdutos'      => 'R$ ' . number_format($totalProdutos, 2, ',', '.'),
            'totalServicos'      => 'R$ ' . number_format($totalServicos, 2, ',', '.'),
            'subtotal'           => 'R$ ' . number_format($subtotal, 2, ',', '.'),
            'desconto'           => $descontoFormatado,
            'descontoReais'      => 'R$ ' . number_format($valorDesconto, 2, ',', '.'),
            'valorTotal'         => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
            'tipoDesconto'       => $os->tipo_desconto
        ];
    }


    public function calculaVenda($idVenda)
    {
        if (!$idVenda) {
            return null;
        }

        $venda = $this->CI->db->where('idVendas', $idVenda)->get('vendas')->row();
        $produtos = $this->CI->vendas_model->getProdutos($venda->idVendas);
        
        $totalProdutos = array_sum(array_map(fn($p) => $p->preco * $p->quantidade, $produtos));
        
        $subtotal = $totalProdutos;
    
        $valorDesconto = 0;
        if ($venda->tipo_desconto == 'real') {
            $valorDesconto = $venda->desconto;
        } elseif ($venda->tipo_desconto == 'porcento') {
            $valorDesconto = $subtotal * ($venda->desconto / 100);
        }
    
        $valorTotal = max(0, $subtotal - $valorDesconto);

        $descontoFormatado = $venda->tipo_desconto == 'real' 
        ? 'R$ ' . number_format($venda->desconto, 2, ',', '.') 
        : $venda->desconto . ' %';

        return [
            'totalProdutos'      => 'R$ ' . number_format($totalProdutos, 2, ',', '.'),
            'subtotal'           => 'R$ ' . number_format($subtotal, 2, ',', '.'),
            'desconto'           => $descontoFormatado,
            'descontoReais'      => 'R$ ' . number_format($valorDesconto, 2, ',', '.'),
            'valorTotal'         => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
            'tipoDesconto'       => $venda->tipo_desconto
        ];
    }
    // futuramente pode ser movido a função validaCpf e ValidaCnpj
    
}
