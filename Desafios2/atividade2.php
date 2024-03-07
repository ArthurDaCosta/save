<?php 

if (empty($params['filtros']['idevento'])) 
    throw new Exception('Defina um Evento');

$acessos = Db::read()
    ->select([
        "cev.dscevento",
        "tip.dscacesso_tipo",
        "cat.dsccategoria",
        "COUNT('ace.idacesso') as num_acessos"
    ])
    ->from('circus_evento cev')
    ->leftJoin('circus_acesso ace', 'cev.idevento = ace.idevento')
    ->leftJoin('circus_acesso_tipo tip', 'ace.idacesso_tipo = tip.idacesso_tipo')
    ->leftJoin('circus_categoria cat', 'tip.idcategoria = cat.idcategoria')
    ->whereAND(['cev.idevento' => $params['filtros']['idevento']])
    ->groupBy([1, 2, 3])
    ->fetchAll();

return ['rows' => $acessos];