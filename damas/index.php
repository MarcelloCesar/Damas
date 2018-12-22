<html>
    <head>       
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
        var nextMove = 'white';

        function buildStage(){            
            stage = document.getElementById('board');                       
            
            var whitePieceId = 0;
            var blackPieceId = 19;
                        
            var DarkStage = true;
            for (var line = 10; line>0; line--){
                for (var column = 'a'; column != 'k'; column = String.fromCharCode(column.charCodeAt(0) + 1)){
                    var div = document.createElement("div");
                    var stageId = column + line;   
                    div.id = stageId;
                    div.addEventListener("click", stageClick);
                    div.classList.add("stage"); 
                    
                    if (DarkStage){
                        if(line < 5){
                            piece = document.createElement("div");
                            piece.id = "whitePiece" + whitePieceId++;
                            piece.classList.add("whitePiece");
                            piece.classList.add("piece");
                            piece.style.cursor = "pointer";
                            piece.addEventListener("click", pieceClick);
                            div.appendChild(piece);
                        } else if (line > 6){
                            piece = document.createElement("div");
                            piece.id = "blackPiece" + blackPieceId--;
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

        function pieceClick(event){            
            piece = event.target;
            stage = document.getElementById(piece.id).parentElement;
            stageLetter = stage.id.slice(0, 1);
            stageNumber = stage.id.slice(1);

            
            console.log(stage.id)
            
            var inicialPossibleMoves = []
            

            cleanHighlightedStages();

            if(piece.classList.contains("whitePiece")){
                inicialPossibleMoves.push(String.fromCharCode(stageLetter.charCodeAt(0) - 1) + (parseInt(stageNumber) + 1))
                inicialPossibleMoves.push(String.fromCharCode(stageLetter.charCodeAt(0) + 1) + (parseInt(stageNumber) + 1))
            } else {
                inicialPossibleMoves.push(String.fromCharCode(stageLetter.charCodeAt(0) - 1) + (parseInt(stageNumber) - 1))
                inicialPossibleMoves.push(String.fromCharCode(stageLetter.charCodeAt(0) + 1) + (parseInt(stageNumber) - 1))
            }            
            console.log(inicialPossibleMoves);
            avaliaPosicao(inicialPossibleMoves);                       
            lastSelectedPiece = piece;
        }   

        function avaliaPosicao(inicialPossibleMoves){
            for(var i=0; i<inicialPossibleMoves.length; i++){
                var goToStage = document.getElementById(inicialPossibleMoves[i]);
                try{
                    var children  = goToStage.childNodes;
                }catch(TypeError){
                    continue;
                }
                var sameColorPieceExists  = false;
                var opostColorPieceExists = false;
                for(var j=0; j<children.length; j++){
                    if (children[j].classList.contains(getColorClass(piece))){
                        sameColorPieceExists = true;
                        break;
                    } else if (children[j].classList.contains(getOpostColorClass(piece))){
                        opostColorPieceExists = true;
                        break;
                    }
                }
                
                if(!sameColorPieceExists && !opostColorPieceExists){
                    document.getElementById(inicialPossibleMoves[i]).classList.add('highlightedStage');
                } else if (opostColorPieceExists){
                    var stageToAvail = '';
                    
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

                    cleanHighlightedStages();
                    lastSelectedPieceId = null;
                    nextMove = nextMove == 'white' ? 'black' : 'white';
                }
            }
        }
        buildStage();       
        
    </script>    
</html>