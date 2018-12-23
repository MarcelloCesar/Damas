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
            $stage = $_POST['stage'];
            if(empty($stage)){
                throw new Exception("Peça selecionada inválida");
            }
            
            $moves = getPossibleMoves($board, $stage, false);
            echo json_encode(array("type" => "response", 'message' => $moves));
            break;        

        case 'computerMove':
            $move = getComputerMove($board);
            echo json_encode(array("type" => "response", 'message' => $move));
            break;
    }    

} catch (Exception $exc){
    $response = array("type" => "exception", "message" => $exc->getMessage());
    echo json_encode($response);
}

function getPossibleMoves($board, $stage, $recursive = false){
    $stageInf = getStageInf($board, $stage);    
    $possibleMoves = array();
    $itsGonnaEat   = array();
    $stageToGo     = chr(ord($stageInf['stageLetter']) +1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);
    $stageToGoInf  = getStageInf($board,$stageToGo);
    if((ord(explode("_", $stageToGo)[0]) >= 97) && (ord(explode("_", $stageToGo)[0]) <= 106) &&
       (explode("_", $stageToGo)[1] >=1) &&  (explode("_", $stageToGo)[1] <= 10)){
        if(!empty($stageToGoInf['piece'])){
            if($stageToGoInf['piece']['color'] != $stageInf['piece']['color']){
                $stageToGo = chr(ord($stageInf['stageLetter']) +2) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -2 : $stageInf['stageNumber'] +2);
                $stageToGoInf = getStageInf($board,$stageToGo);
                if( empty($stageToGoInf['piece'])){
                    $itsGonnaEat[]   = chr(ord($stageInf['stageLetter']) +1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);
                    $possibleMoves[] = array($stageToGo, $itsGonnaEat);
                    setStagePiece($board, $stageToGo, $stageInf['piece']);
                    $recursiveMoves  = getPossibleMoves($board, $stageToGo, true);

                    if(!empty($recursiveMoves)){
                        foreach($recursiveMoves as $recMove){
                            $possibleMoves[] = array($recMove[0], array_merge($itsGonnaEat, $recMove[1]) );
                        }
                    }                    
                    setStagePiece($board, $stageToGo, null);
                }
            }
        } else {
            if(!$recursive){            
                $possibleMoves[] = array($stageToGo, array());
            }
        }
    }

    $itsGonnaEat   = array();
    $stageToGo    = chr(ord($stageInf['stageLetter']) -1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);   
    $stageToGoInf = getStageInf($board,$stageToGo);
    if((ord(explode("_", $stageToGo)[0]) >= 97) && (ord(explode("_", $stageToGo)[0]) <= 106) &&
       (explode("_", $stageToGo)[1] >=1) &&  (explode("_", $stageToGo)[1] <= 10)){
        if(!empty($stageToGoInf['piece'])){
            if($stageToGoInf['piece']['color'] != $stageInf['piece']['color']){
                $stageToGo = chr(ord($stageInf['stageLetter']) -2) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -2 : $stageInf['stageNumber'] +2);   
                $stageToGoInf = getStageInf($board,$stageToGo);
                if( empty($stageToGoInf['piece'])){
                    $itsGonnaEat[]   = chr(ord($stageInf['stageLetter']) -1) . "_" . ($stageInf['piece']['color'] == 'black'? $stageInf['stageNumber'] -1 : $stageInf['stageNumber'] +1);
                    $possibleMoves[] = array($stageToGo, $itsGonnaEat);
                    setStagePiece($board, $stageToGo, $stageInf['piece']);
                    $recursiveMoves  = getPossibleMoves($board, $stageToGo, true);

                    if(!empty($recursiveMoves)){
                        foreach($recursiveMoves as $recMove){
                            $possibleMoves[] = array($recMove[0], array_merge($itsGonnaEat, $recMove[1]) );
                        }
                    }                    
                    setStagePiece($board, $stageToGo, null);                    
                }
            }
        } else {
            if(!$recursive){
                $possibleMoves[] = array($stageToGo, array());
            }
        }
    }
    
    return $possibleMoves;
}

function getStageInf($board, $stagePos){

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

function setStagePiece(&$board, $stagePos, $piece){
    foreach($board as $stage){
        if($stage->stage == $stagePos){
            if(empty($piece)){
                $stage->piece = '';
            } else {
                $stage->piece        = new stdClass();
                $stage->piece->id    = $piece['id'];
                $stage->piece->color = $piece['color'];
            }
            break;
        }        
    }
}

function getComputerMove($board){

    $moves = array();
    foreach($board as $b){
        $ss = getStageInf($board, $b->stage);
        if(!empty($ss['piece']) && $ss['piece']['color'] == 'black')
            $moves [] = array($ss['piece']['id'], getPossibleMoves($board, $b->stage, false));
    }
    $move = rand(0, 19);  
    $letsgo = false;
    while(!$letsgo){
        $move = rand(0, 19);  
        if(!empty($moves[$move][1])){
            $letsgo = true;
        }
    }
    
    return array($moves[$move][0], $moves[$move][1][0]); 
}