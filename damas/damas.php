<?php

ini_set('display_errors', 1);
try{
    $action = $_POST['action'];
    $board  = json_decode($_POST['board']);
    if(empty($action)){
        throw new Exception ("Ação inválida.");
    } else if (emptY($board)){
        throw new Exception ("Tabuleiro em estado inválido");
    }      

    switch($action){
        case 'getPossibleMoves':
            getPossibleMoves();
            break;        
    }    

} catch (Exception $exc){
    $response = array("type" => "exception", "message" => $exc->getMessage());
    echo json_encode($response);
}

function getPossibleMoves(){
    global $board;
    $stageSelected = $_POST['stage'];        
    if(empty($stageSelected)){
        throw new Exception("Peça selecionada inválida");
    }

    $stageInf = getStageInf($stageSelected);    
    //avaliaPosicao($stageSelected, $stageInf['color']);
    // Define os movimentos iniciais padrão    
    // ------adicionar veriicacao maior / menor tabuleiro (Saindo do tabbulerio)
    // ---- fazer a parte recursiva 
    $possibleMoves = array();
    $itsGonnaEat   = array();
    $stageToGo     = chr(ord($stageInf['stageLetter']) +1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);
    $stageToGoInf  = getStageInf($stageToGo);
    if(!empty($stageToGoInf['piece'])){
        if($stageToGoInf['piece']['color'] != $stageInf['piece']['color']){
            $stageToGo = chr(ord($stageInf['stageLetter']) +2) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -2 : $stageInf['stageNumber'] +2);
            $stageToGoInf = getStageInf($stageToGo);
            if( empty($stageToGoInf['piece'])){
                $itsGonnaEat[]   = array($stageToGo, chr(ord($stageInf['stageLetter']) +1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1));
                $possibleMoves[] = $stageToGo;
            }
        }
    } else {
        $possibleMoves[] = $stageToGo;
    }

    $stageToGo    = chr(ord($stageInf['stageLetter']) -1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);   
    $stageToGoInf = getStageInf($stageToGo);
    if(!empty($stageToGoInf['piece'])){
        if($stageToGoInf['piece']['color'] != $stageInf['piece']['color']){
            $stageToGo = chr(ord($stageInf['stageLetter']) -2) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -2 : $stageInf['stageNumber'] +2);   
            $stageToGoInf = getStageInf($stageToGo);
            if( empty($stageToGoInf['piece'])){
                $itsGonnaEat[]   = array($stageToGo, chr(ord($stageInf['stageLetter']) -1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1));
                $possibleMoves[] = $stageToGo;
            }
        }
    } else {
        $possibleMoves[] = $stageToGo;
    }

    echo json_encode(array("type" => "response", 'message' => $possibleMoves, 'itsGonnaEat' => $itsGonnaEat));
}

function avaliaPosicao($stage, $color){
    $possibleMoves = array();
    $possibleMoves[] = chr(ord($stageInf['stageLetter']) +1) . "_" . ($stageInf['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);    
    $possibleMoves[] = chr(ord($stageInf['stageLetter']) -1) . "_" . ($stageInf['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);   
    $pieceThere = getStageInf($stage);
    if(empty($pieceThere)){
        return $stage;
    } 

    
}

function getStageInf($stagePos){
    global $board;

    foreach($board as $stage){
        if($stage->stage == $stagePos){
            $result = array();
            $result['stage'] = $stage->stage;
            $stageInf = explode("_", $result['stage']);
            $result['stageLetter'] = $stageInf[0];
            $result['stageNumber'] = $stageInf[1];
            if ($stage->piece == ''){
                $result['piece'] =  null ;
            } else {
                $result['piece']          = array();
                $result['piece']['id']    = $stage->piece->id;
                $result['piece']['color'] = $stage->piece->color;                                
            }
            return $result;
        }
    }
}