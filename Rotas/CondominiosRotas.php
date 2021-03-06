<?php
/**
 * Controlador do Condomínios
 * 
 * @author Jônathas Assunção
 * @version 0.0.6
 *
  * =================================================================
 * date - 12/12/2018 - @version 0.0.6
 * description: Mudoa a forma de baixar um parâmetro da requisição
 * =================================================================
 * date - 09/12/2018 - @version 0.0.5
 * description: Muda chamada da view, tem que passar o code_http resposta
 *              Nome da Views, não precisa mais do caminho absoluto 
 * =================================================================
 * date - 08/12/2018 - @version 0.0.4
 * description: Implementação da rota Condominio.adicionar
 * =================================================================
 * date - 04/12/2018 - @version 0.0.3
 * description: Implementação da rota Condominio.excluir e Condominio.confirmaExcluir
 * =================================================================
 * date - 23/11/2018 - @version 0.0.2
 * description: Muda a forma da resposta para o HTML 
 * =================================================================
 * date - 02/10/2018 - @version 0.0.1
 * description: Versão inicial do arquivo, 
 *              criação do método listar que 
 *              exibe a página HTML no navegador referente ao condominio
 * =================================================================
 * 
 * Dir  - Rotas
 * File - CondominiosRotas.php
 */
 
 //Inclui a classe model de negócio
 require_once 'Models/CondominiosModel.php';

 class CondominiosRotas{
   
    /**
     * Rota Condominio.selecionar
     * Exibe a tela de Selecinar um condomínio
     * 
     */
    public function selecionarAction(){

        //Solicita ao Model os condomínios que podem ser listados, e exibe na tela
        try{                       

            //Instancia a classe model, para pegar os dados para serem exibidos
            $cond = new CondominioModel();        
            $dados = $cond->getCondominios();             
            
            //Monta a página HTML e devolve para o cliente
            $view = new Views(200,'Sistema/Admin/selecionarView', Array("header"=> "Escolha um Condomínio!", "dados"=> $dados));                
            $view->imprimirHTML();
            
        }catch(CondominioVazioException $e){
            //Trata a Exceção quando não há condomínio a ser exibido

            //Exibe um modal avisando que não há condomínio e mostra um botão para adicionar
            
            //Define as variáveis para exibição do modal
            $titulo = "Listar Condomínio";
            $mensagem = "Não há condomínio a ser listado, deseja adicionar um novo condomínio?";
            $acao = 'requisitarServidor("index.php", "Condominios.condominio", "titulo=Adicionar Condomínio", "body_resposta"); return false;';
            
            //Monta a página HTML e devolve para o cliente (Monta um modal)
            $view = new Views(200,'Sistema/Admin/modalConfirmaView', Array("header"=> $titulo,"body"=> $mensagem, "action" =>$acao));        
            $view->imprimirHTML();

        }catch(Exception $e){
            //Trata as demais Exceções geradas

            //Pega a mensagem de erro gerada na exceção e retorna um Modal de erro com a mensagem
            $view = new Views(200,'Sistema/Admin/modalErroView', Array("header"=> "Falha ao Listar os Condomínios!","body"=> "Motivo: ".$e->getMessage()));
            $view->imprimirHTML();
        } 
    }    

    /**
     * Rota Condominio.condominio
     * Exibe a tela de Editar condominio
     * 
     */
    public function condominioAction(){
        $view = new Views(200,'Sistema/Admin/CondominiosView', Array("route" => Roteador::baixarParametro('route')));        
        $view->imprimirHTML();        
    }  

    /**
     * Rota Condominio.mudarCond
     * Pega o condominio selecionado na requisição, recarrega a tela principal, porém agora exibe o condomínio selecionado
     */
    public function mudarCondAction(){
        //Solicita ao Model os condomínios que podem ser listados, e exibe na tela
        try{ 
            //Salva o condominio na session                    
            $cnpj = substr(Roteador::baixarParametro('cond'), 0, 18);
            $cond = substr(Roteador::baixarParametro('cond'), 20);
            $condominio = new CondominioModel(); 
            
            if($cnpj == null) throw new Exception("Selecione um condomínio!");
            
            //Verifica se o usuário que está logado tem permissão para acessar o controller e Action solicitado
            if($condominio->temPermissaoMudarCondominio('acessar', $cnpj)){ 
                /**Se entrou no IF, é porque tem a permissão... 
                 * Agora modifica os parametros da Requisição e tenta importar o controller, e chamar a função Action solicitada
                */
                //Recarrega a página
                $_SESSION['cnpj'] = $cnpj;
                $_SESSION['cond'] = $cond.' ( '.$cnpj.' )';
                Roteador::definirRotas('Home.listar'); //Redireciona a rota para a página de principal do sistema
                Roteador::recarregarClient();          //Usuario não está logado, redireciona para tela de login 
            }else{ 
                /**
                 * Se Entrou no ELSE, então o usuário não tem permissão para acessar esse controller e Action
                 * Exibe um Modar de Atenção, informando a mensagem retornada pela Class LoginModel()
                 */
                $view = new Views(201,'Sistema/Admin/modalAtencaoView', Array("header"=> "Esse condomínio não pode ser selecionado!","body"=> "Motivo: ".$login->getMensagem()));
                $view->imprimirHTML();
            }
        }catch(Exception $e){//Trata as Exceções geradas

            //Pega a mensagem de erro gerada na exceção e retorna um Modal de erro com a mensagem
            $view = new Views(201,'Sistema/Admin/modalErroView', Array("header"=> "Falha ao Listar os Condomínios!","body"=> "Motivo: ".$e->getMessage()));
            $view->imprimirHTML();
        } 
        
    }   

################################################################################################################################################################################################
    /**
     * Rota Condominio.buscarCond
     * Exibe a tela de listar o condominio
     * 
     */ 
    public function buscarCondAction(){
        
        //Tenta baixar o condomínio da requisição, e exibe uma janela pedindo confirmação de exclusão.
        try{
            /** 
             * Baixa as variáveis que o HTML enviou na requisição.
             * 
             */
            $cond = Roteador::baixarParametro('cond'); 
            
            /**
             * Para excluir um condomínio, será necessário informar o cnpj do condomínio a ser excluído.
             * Verifica se conseguiu baixar o CNPJ da requisição, ou se realmente foi passado o CNPJ na requisição (Se tem algum condomínio selecionado)
             * 
             * Caso a $cond == null, então não foi passado um cnpj para a requisição, então gera exceção
             *
             */
            if($cond == null) throw new Exception("Selecione um condomínio primeiro!");
            
            $condominio = new CondominioModel();//Criar conecção com Model
            $dadosCondominio = $condominio->listarCondominio($cond); //View recebe o retorno da busca do Model

            $view = new Views(200,'Sistema/Admin/ListarCondominiosView', $dadosCondominio);
            $view->imprimirHTML();

        }catch(Exception $e){//Trata as Exceções geradas

            //Pega a mensagem de erro gerada na exceção e retorna um Modal de erro com a mensagem
            $view = new Views(201, 'Sistema/Admin/modalErroView', Array("header"=> "Falha ao buscar condomínio!","body"=> "Motivo: ".$e->getMessage()));
            $view->imprimirHTML();
        }

    }
################################################################################################################################################################################################


    /**
     * Rota Condominio.excluir
     * Exibe a um modal pedindo confirmação para excluir o condomínio
     * 
     */
    public function excluirAction(){
        
        //Tenta baixar o condomínio da requisição, e exibe uma janela pedindo confirmação de exclusão.
        try{
            /** 
             * Baixa as variáveis passadas na requisição.
             */
            $cond = Roteador::baixarParametro('cond'); 
            
            /**
             * Para excluir um condomínio, será necessário informar o cnpj do condomínio a ser excluído.
             * Verifica se conseguiu baixar o CNPJ da requisição, ou se realmente foi passado o CNPJ na requisição (Se tem algum condomínio selecionado)
             * 
             * Caso a $cond == null, então não foi passado um cnpj para a requisição, então gera exceção
             *
             */
            if($cond == null) throw new Exception("Selecione um condomínio primeiro!");
            

            //Define as variáveis para exibição do modal
            $titulo = "Confirma excluir o Condomínio?";
            $mensagem = "Ao confirmar, <b>todos</b> os dados do condomínio: $cond, serão excluído!!! <br/> Confirma?";
            $acao = 'requisitarServidor("index.php", "Condominios.confirmaExcluir", "cond='.$cond.'", "modal_resposta"); return false;';
            
            //Monta a página HTML e devolve para o cliente (Monta um modal)
            $view = new Views(200,'Sistema/Admin/modalConfirmaView', Array("header"=> $titulo,"body"=> $mensagem, "action" =>$acao));        
            $view->imprimirHTML();
        }catch(Exception $e){//Trata as Exceções geradas

            //Pega a mensagem de erro gerada na exceção e retorna um Modal de erro com a mensagem
            $view = new Views(201,'Sistema/Admin/modalErroView', Array("header"=> "Falha ao excluir condomínio!","body"=> "Motivo: ".$e->getMessage()));
            $view->imprimirHTML();
        }          
    }  

    //Editar
    /**
     * Rota Condominio.buscarCond
     * Exibe a tela de listar o condominio
     * 
     */ 
    public function editarAction(){
        
        //Tenta baixar o condomínio da requisição, e exibe uma janela pedindo confirmação de exclusão.
        try{
            /** 
             * Baixa as variáveis que o HTML enviou na requisição.
             * 
             */
            $cond = Roteador::baixarParametro('cond'); 
            $rota = Roteador::baixarParametro('route');

            /**
             * Para excluir um condomínio, será necessário informar o cnpj do condomínio a ser excluído.
             * Verifica se conseguiu baixar o CNPJ da requisição, ou se realmente foi passado o CNPJ na requisição (Se tem algum condomínio selecionado)
             * 
             * Caso a $cond == null, então não foi passado um cnpj para a requisição, então gera exceção
             *
             */
            if($cond == null) throw new Exception("Selecione um condomínio primeiro!");
            
            $condominio = new CondominioModel();//Criar conecção com Model
            $dadosCondominio = $condominio->listarCondominio($cond); //View recebe o retorno da busca do Model
            
            //Define a rota para altualizar no banco de dados
            $dadosCondominio["route"] = $rota;
            $view = new Views(200,'Sistema/Admin/CondominiosView', $dadosCondominio);
            $view->imprimirHTML();

        }catch(Exception $e){//Trata as Exceções geradas

            //Pega a mensagem de erro gerada na exceção e retorna um Modal de erro com a mensagem
            $view = new Views(201, 'Sistema/Admin/modalErroView', Array("header"=> "Falha ao buscar condomínio!","body"=> "Motivo: ".$e->getMessage()));
            $view->imprimirHTML();
        }

    }




   /**
     * Rota Condominio.confirmaExcluir
     * 
     * Essa é a rota do condomínio que irá tratar quando for solicitado para um condomínio ser excluído!
     */
    public function confirmaExcluirAction(){

        //Tenta baixar as variáveis da requisição e encaminha para o Model excluir o condomínio
        try{
            /** 
             * Baixa as variáveis que o HTML enviou na requisição.
             */
            $cond = Roteador::baixarParametro('cond'); //O cond virá com o formato: <nome Condominio> (99.999.999/9999-99)
            
            /**
             * Para excluir um condomínio, será necessário informar o cnpj do condomínio a ser excluído
             * Verifica se conseguiu baixar o cond da requisição, ou se realmente foi passado o CNPJ na requisição (Se tem algum condomínio selecionado)
             * 
             * Caso a $cond == null, então não foi passado um condomínio na requisição, então gera exceção
             *
             */
            if($cond == null) throw new Exception("Selecione um condomínio primeiro!");




            //=====Caso chegue aqui, então foi passado o CNPJ na requisição, esse cnpj está na variável $cond, no formato:  <nome Condominio> (99.999.999/9999-99)
            //Agora o condomínio será excluído

            //Instancia a classe Model do Condominio (O Model possui um método para deletar o condomínio)
            $condominio = new CondominioModel(); 
            $condominio->deletarCondominio($cond);
            


            

        //=====Caso chegue aqui, então o condominío foi excluído    
            //Limpa o condomínio da seção (Isso vai fazer com que o Desapareça no HTML o: <nome Condominio> (99.999.999/9999-99) que foi excluido)
            $_SESSION['cnpj'] = null;
            $_SESSION['cond'] = null;
            
            //Recarrega a página (Isso faz com que o servidor atualize a página, removendo os dados do condomínio no sistema)                       
            Roteador::atualizaSubRota('Inicio.listar'); //Mostra a página inicio
            Roteador::definirRotas('Home.listar');      //Redireciona para a Rota Home (O usuário vai ter que escolher outro condomínio...)
            Roteador::recarregarClient();               //Recarrega a página para redirecionar para a página Home

        }catch(Exception $e){//Trata as Exceções geradas

            //Pega a mensagem de erro gerada na exceção e retorna um Modal de erro com a mensagem
            $view = new Views(200,'Sistema/Admin/modalErroView', Array("header"=> "Falha ao excluir condomínio!","body"=> "Motivo: ".$e->getMessage()));
            $view->imprimirHTML();
        }        
        
    } 
    

   /**
     * Rota Condominio.adicionar
     * 
     * Essa é a rota do condomínio que irá adicionar um novo condomínio
     */    
    public function adicionarAction(){
        try{
            //Baixa os parâmetros passados na requisição 
            $razaoSocial = Roteador::baixarParametro('razaoSocial');
            $cnpj        = Roteador::baixarParametro('cnpj');
            $telefone    = Roteador::baixarParametro('telefone');
            $celular     = Roteador::baixarParametro('celular');
            $email       = Roteador::baixarParametro('email');
            $cep         = Roteador::baixarParametro('cep');
            $rua         = Roteador::baixarParametro('rua');
            $numero      = Roteador::baixarParametro('numero');
            $setor       = Roteador::baixarParametro('setor');
            $complemento = Roteador::baixarParametro('complemento');
            $municipio   = Roteador::baixarParametro('municipio');
            $uf          = Roteador::baixarParametro('uf');
            $bancos      = Roteador::baixarParametro('bancos');
            
            if($cnpj == null||$razaoSocial == null||$email == null) throw new Exception("Preecha todos os campos obrigatórios!<br/><h5>*Razão Social;</h5><h5>*CNPJ;</h5><h5>*E-mail;</h5>");

            $condominio = new CondominioModel();
            $condominio->adicionarCondominio($razaoSocial, $cnpj, $telefone, $celular, $email, $cep, $rua, $numero, $setor, $complemento, $municipio, $uf, $bancos);



            //Após adicionar o condomínio, será exibido um  modal... E quando clicar em 'Fechar', irá redirecionar para a rota Condominios.buscarCond
                //Muda o condomínio selecionado para o condomínio que foi inserido
                Roteador::adicionaParametro("cond=$razaoSocial ( ".mascara($cnpj, '##.###.###/####-##')." )"); //Passa os parâmetros para a rota

                //Define as variáveis para exibição do modal
                $titulo = "Condomínio inserido com sucesso!";
                $mensagem = "O condomínio: <b>$razaoSocial ($cnpj)</b> foi inserido com sucesso!";            

                //Quando clicar em 'Fechar' no modal, Atualizará o campo do condomínio selecionado no HTML e será redirecionado para a rota Condominios.buscarCond
                $mudarCondominio = '$("#selectCond").val("'.$razaoSocial.' ( '.mascara($cnpj, '##.###.###/####-##').' )"); ';
                $acao = $mudarCondominio.'requisitarServidor("index.php", "Condominios.buscarCond", "cond='.$razaoSocial.' ( '.mascara($cnpj, '##.###.###/####-##').' )" , "body_resposta");';
            

            $view = new Views(200,'Sistema/Admin/modalSuccessView', Array("header"=>$titulo, "body"=>$mensagem, "action"=>$acao));
            $view -> imprimirHTML();
                        

        }catch(Exception $e){

            $view = new Views(200,'Sistema/Admin/modalErroView', Array("header"=>"Falha ao inserir o condomínio", "body"=>"Motivo: ".$e->getMessage()));
            $view -> imprimirHTML();

        }

    }

    /**
     * Rota Condominio.adicionar
     * 
     * Essa é a rota do condomínio que irá adicionar um novo condomínio
     */    
    public function atualizarAction(){
        try{
            //Baixa os parâmetros passados na requisição 
            $razaoSocial = Roteador::baixarParametro('razaoSocial');
            $cnpj        = Roteador::baixarParametro('cnpj');
            $telefone    = Roteador::baixarParametro('telefone');
            $celular     = Roteador::baixarParametro('celular');
            $email       = Roteador::baixarParametro('email');
            $cep         = Roteador::baixarParametro('cep');
            $rua         = Roteador::baixarParametro('rua');
            $numero      = Roteador::baixarParametro('numero');
            $setor       = Roteador::baixarParametro('setor');
            $complemento = Roteador::baixarParametro('complemento');
            $municipio   = Roteador::baixarParametro('municipio');
            $uf          = Roteador::baixarParametro('uf');
            $bancos      = Roteador::baixarParametro('bancos');
                      
            if($cnpj == null||$razaoSocial == null||$email == null) throw new Exception("Preecha todos os campos obrigatórios!<br/><h5>*Razão Social;</h5><h5>*CNPJ;</h5><h5>*E-mail;</h5>");

            $condominio = new CondominioModel();            
            $condominio->atualizarCondominio($razaoSocial, $cnpj, $telefone, $celular, $email, $cep, $rua, $numero, $setor, $complemento, $municipio, $uf, $bancos);

            //Após atualizar o condomínio, será exibido um  modal... E quando clicar em 'Fechar', irá redirecionar para a rota Condominios.buscarCond
            
                //Define as variáveis para exibição do modal
                $titulo = "Condomínio atualizado com sucesso!";
                $mensagem = "O condomínio: <b>$razaoSocial ($cnpj) </b> foi atualizado com sucesso!";            

                //Quando clicar em 'Fechar' no modal, Atualizará o campo do condomínio selecionado no HTML e será redirecionado para a rota Condominios.buscarCond
                $mudarCondominio = '$("#selectCond").val("'.$razaoSocial.' ( '.$cnpj.' )"); ';
                $acao = $mudarCondominio.'requisitarServidor("index.php", "Condominios.buscarCond", "cond='.$razaoSocial.' ( '.$cnpj.' )" , "body_resposta");';
            

            $view = new Views(200,'Sistema/Admin/modalSuccessView', Array("header"=>$titulo, "body"=>$mensagem, "action"=>$acao));
            $view -> imprimirHTML();            
            /*
            Roteador::atualizaSubRota('Condominios.buscarCond');          //Define a rota que trata a listagem do condomínio
            Roteador::adicionaParametro("cond=$razaoSocial ( ".mascara($cnpj, '##.###.###/####-##')." )"); //Passa os parâmetros para a rota
            Roteador::definirRotas('Home.listar');                        //Define uma rota Master (Pois a página do navegador será recarregada)
            Roteador::recarregarClient();                                 //Recarrega o cliente e executa a rota solicitada.
            */
        }catch(Exception $e){

            $view = new Views(200,'Sistema/Admin/modalErroView', Array("header"=>"Falha ao inserir o condomínio", "body"=>"Motivo: ".$e->getMessage()));
            $view -> imprimirHTML();

        }

    }
 }
?>