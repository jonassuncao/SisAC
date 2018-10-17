<?php
/**
 * Controlador do Condominios
 * 
 * @author Jonathas Assunção
 * @version 0.0.2
 * 
 * * =================================================================
 * date - 17/10/2018 - @version 0.0.2
 * description: Adicionado função selecionarAction 
 *              (Exibe a página SelecionarView.phtml em um modal);
 * =================================================================
 * date - 02/10/2018 - @version 0.0.1
 * description: Versão inicial do arquivo, 
 *              criação do método listar que 
 *              exibe a página HTML no navegador referente ao condominio
 * =================================================================
 * 
 * Dir  - Controllers
 * File - CondominiosController.php
 */
 
 //Inclui a classe model de negócio
 require_once 'Models/CondominiosModel.php';

 class CondominiosController{

    /**
     * Exibe a tela de Categoria
     * 
     */
    public function listarAction(){
        //Renderiza a página de Listar Condominios
        $view = new ViewMaster('Views/Sistema/admin/ListarCondominiosView.phtml');
        //Retorna para o navegador a página HTML à ser exibida.
        $view->showHTMLPag();
        
    }
    
    /**
     * Exibe a tela de Categoria
     * 
     */
    public function selecionarAction(){
        //Renderiza a página de Selecionar Condominios
        $view = new ViewMaster('Views/Sistema/admin/SelecionarView.phtml', Array("header"=> "Escolha um Condomínio!"));
        //Retorna para o navegador a página HTML à ser exibida.
        $view->showHTMLPag();
        
    }    

        /**
     * Exibe a tela de Categoria
     * 
     */
    public function mudarCondAction(){
        //Salva o condominio na session        
        $condominio = substr($_POST['cond'], 0, 18);
        $login = new LoginModel(); //Instancia uma classe Login

        //Verifica se o usuário que está logado tem permissão para acessar o controller e Action solicitado
        if($login->isPermissao('acessar', $condominio)){ 
            /**Se entrou no IF, é porque tem a permissão... 
             * Agora modifica os parametros da Requisição e tenta importar o controller, e chamar a função Action solicitada
            */
            //Recarrega a página
            $_SESSION['cond'] = $condominio;
            ControllerMaster::setRequest('Home', 'listar');  
            ControllerMaster::redirect(); 
        }else{ 
            /**
             * Se Entrou no ELSE, então o usuário não tem permissão para acessar esse controller e Action
             * Exibe um Modar de Atenção, informando a mensagem retornada pela Class LoginModel()
             */
            $view = new ViewMaster('Views/Sistema/admin/modalAtencaoView.phtml', Array("header"=> "Esse condomínio não pode ser selecionado!","body"=> "Motivo: ".$login->getMensagem()));
            $view->showHTMLPag();
        }

        
    }   
 }
?>