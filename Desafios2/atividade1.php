<?php

function WebServiceExec($params, $data){ 
	
    if(!isset($data["num_contrato"]))
        throw new Exception("Informe o número do contrato");
    
    $parcelas = Db::ReadOnly()
        ->select([
            'con.idcontrato',
            'pes.cpf',
            'con.num_contrato',
            'cta.valor',
            'cta.valor_desconto',
            'cta.valor_desconto_manual',
            'cta.valor_juros',
            'cta.valor_multa',
            'cta.valor_adesao',
            'cta.valor_acrescimo',
            'cta.valor_cancelado',
            'cta.valor_pago',
            'cta.valor_reajuste',
            'cta.dt_vencimento',
            'cta.valor_juros_parc'
        ])
        ->from("gs_contrato con")
        ->join("cx_conta_receber cta", "cta.idcontrato = con.idcontrato")
        ->join("base_pessoa pes", "pes.idpessoa = con.idpessoa")
        ->whereAND([
            "con.num_contrato" => $data["num_contrato"],
            'cta.status' => 'AP'
        ])
        ->orderByASC('cta.dt_vencimento')
        ->fetchAll();
    
    if(!$parcelas)
        throw new Exception("Não foi encontrado um contrato de número ". $data['num_contrato'] ." com parcelas não pagas.");
    
	$retorno = [];
    foreach($parcelas as &$parcela) {
        $retorno[] = [
            'cpf' => $parcela['cpf'],
            'num_contrato' => $parcela['num_contrato'],
            'dt_vencimento' => we_formatarData($parcela['dt_vencimento']),
            'valor_desconto' => number_format($parcela['valor_desconto'], 2, ',', '.'),
            'valor_desconto_manual' => number_format($parcela['valor_desconto_manual'], 2, ',', '.'),
            'valor_juros' => number_format($parcela['valor_juros'], 2, ',', '.'),
            'valor_multa' => number_format($parcela['valor_multa'], 2, ',', '.'),
            'valor_adesao' => number_format($parcela['valor_adesao'], 2, ',', '.'),
            'total' => number_format((
                + $parcela['valor'] 
                - $parcela['valor_desconto'] 
                - $parcela['valor_desconto_manual'] 
                + $parcela['valor_juros'] 
                + $parcela['valor_multa'] 
                + $parcela['valor_adesao'] 
                + $parcela['valor_acrescimo'] 
                - $parcela['valor_cancelado'] 
                - $parcela['valor_pago'] 
                + $parcela['valor_reajuste'] 
                + $parcela['valor_juros_parc']
            ), 2, ',', '.')
        ];
    }
    
	return $retorno;
}
