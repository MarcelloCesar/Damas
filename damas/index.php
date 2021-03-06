<html>
    <head>       
        <script type="text/javascript" src="util.js"></script>
        <script type="text/javascript" src="jquery.js"></script>
        <title>Jogo de Damas</title>
        <style>
            .DarkStage {
                background-color: #964b00;
            }

            .LightStage {
                background-color: #DEB887;
            }

            .stage {
                float: left;
                border-color: black;
                border-style: solid;     
                border-width: 1px;    
                width: 60px;
                height: 60px;
            }

            .highlightedStage {
                background: #957a56;
            }

            .piece{
                width: 50px;
                height: 50px;
                margin: 5px;
                border-radius: 30px;
            }
            .whitePiece {
                background-color: white;
               /* border-color: #AAAAAA;
                border-width: 2px;                
                border-style: solid;*/
            }

            .blackPiece {
                background-color: black;
            }

            #board {                
                position: absolute;
                left: 200px;
                top: 10px;                
                padding : 0px;
                margin: 0px;
                float: left;
                width:  642px;
            }


        </style>
    </head>
    <body>
        <div>
            <div id="board" class="board">  
            </div>
        </div>
    </body>

    <script type="text/javascript">      
        var lastSelectedPiece = null;
        var nextMove    = 'white';
        var moves       = [];
        var pvp         = false;

        function buildStage(){            
            stage = document.getElementById('board');                       
            
            var whitePieceId = 0;
            var blackPieceId = 19;
                        
            var DarkStage = true;
            for (var line = 10; line>0; line--){
                for (var column = 'a'; column != 'k'; column = String.fromCharCode(column.charCodeAt(0) + 1)){
                    var div = document.createElement("div");
                    var stageId = column + "_" + line;   
                    div.id = stageId;
                    div.addEventListener("click", stageClick);
                    div.classList.add("stage"); 
                    
                    if (DarkStage){
                        if(line < 5){
                            piece = document.createElement("div");
                            piece.id = "whitePiece_" + whitePieceId++;
                            piece.classList.add("whitePiece");
                            piece.classList.add("piece");
                            piece.style.cursor = "pointer";
                            piece.addEventListener("click", pieceClick);
                            div.appendChild(piece);
                        } else if (line > 6){
                            piece = document.createElement("div");
                            piece.id = "blackPiece_" + blackPieceId--;
                            piece.classList.add("blackPiece");
                            piece.classList.add("piece");
                            piece.style.cursor = "pointer";
                            piece.addEventListener("click", pieceClick);
                            div.appendChild(piece);
                        }
                        div.classList.add("DarkStage");
                    } else {
                        div.classList.add("LightStage");
                    }                  
                    DarkStage = !DarkStage;
                    stage.appendChild(div);                    
                }
                DarkStage = !DarkStage;
            }
        }

        function boardToJson(){
            var JSONMessage = '{';
            var boardObjs   = [];

            var stages = document.getElementsByClassName('stage');
            for(var i=0; i<stages.length; i++){
                var stageObj = {};
                stageObj.stage  = stages[i].id;
                if (stages[i].childNodes[0] != undefined){
                    stageObj.piece = {}
                    stageObj.piece.id      = stages[i].childNodes[0].id;
                    stageObj.piece.color   = stages[i].childNodes[0].classList.contains("blackPiece") ? 'black' : 'white';
                } else {
                    stageObj.piece  = '';
                }   
                boardObjs.push(stageObj);
            }
            
            return JSON.stringify(boardObjs);            
        }

        function pieceClick(event){
            var stringRequest = "action=getPossibleMoves&board=" + boardToJson() + "&stage="+event.target.parentElement.id;
            post("damas.php", stringRequest, function (response){                
                response = JSON.parse(response.responseText);
                cleanHighlightedStages();
                if(response.type == 'exception'){
                    alert('Ocorreu o seguinte erro ao tentar selecionar a peça: ' + response.message);
                } else {                   
                    highlightStages(response.message);
                    lastSelectedPiece = event.target;
                    moves             = response.message; 
                }               
            });
        }

        function highlightStages(stages){
            for(var i=0; i<stages.length; i++){                
                try{
                    document.getElementById(stages[i][0]).classList.add('highlightedStage');
                }catch(Exception){
                    continue;
                }
            }
        }

        function getColorClass(piece){
            return piece.classList.contains('whitePiece') ? 'whitePiece' : 'blackPiece';
        }

        function getOpostColorClass(piece){
            return getColorClass(piece) == 'whitePiece' ? 'blackPiece' : 'whitePiece';
        }

        function cleanHighlightedStages(){
            var higlighted = document.getElementsByClassName('highlightedStage');
            while(higlighted.length > 0){
                higlighted[0].classList.remove('highlightedStage');
            }
        }
        
        function stageClick(event){
            var stageSender = event.target;            
            if(stageSender.classList.contains("highlightedStage") && lastSelectedPiece){
                if((lastSelectedPiece.classList.contains('whitePiece') && nextMove == 'white') ||
                   (lastSelectedPiece.classList.contains('blackPiece') && nextMove == 'black')){
                    stageSender.appendChild(document.getElementById(lastSelectedPiece.id));

                    for(var i=0; i<moves.length; i++){
                        if(moves[i][0] == stageSender.id){
                            for(var j=0; j<moves[i][1].length; j++){       
                                document.getElementById(moves[i][1][j]).innerHTML ='';
                            }
                        }
                    }

                    cleanHighlightedStages();
                    lastSelectedPieceId = null;
                    itsGonnaEat         = null;
                    nextMove = nextMove == 'white' ? 'black' : 'white';
                    if(!pvp && nextMove == 'black'){
                        computerMove();
                        nextMove = nextMove == 'white' ? 'black' : 'white';
                    }
                }
            }
        }

        function computerMove(){
            var stringRequest = "action=computerMove&board=" + boardToJson(); 
            post("damas.php", stringRequest, function(response){
                response = JSON.parse(response.responseText);
                var pieceToMove = document.getElementById(response.message[0]);
                var newStage    = document.getElementById(response.message[1][0]);                
                newStage.appendChild(pieceToMove);
                lastSelectedPiece = pieceToMove;

                for(var j=0; j<response.message[1][1].length; j++){       
                    document.getElementById(response.message[1][1][j]).innerHTML ='';
                }           
                
            });
        }
        buildStage();       
        
    </script>    
</html>