<?php
/**
 * Controlador dos Usuários
 * 
 * @author Jônathas Assunção
 * @version 0.0.3
 *
 * =================================================================
 * date - 09/12/2018 - @version 0.0.3
 * description: Muda chamada da view, tem que passar o code_http resposta
 *              Nome da Views, não precisa mais do caminho absoluto
 * =================================================================
 * date - 23/11/2018 - @version 0.0.2
 * description: Muda chamada da view
 * =================================================================
 * date - 02/10/2018 - @version 0.0.1
 * description: Versão inicial do arquivo, 
 *              criação do método listar que 
 *              exibe a página HTML no navegador referente aos Usuários
 * =================================================================
 * 
 * Dir  - Rotas
 * File - UsuariosRotas.php
 */
 
 //Inclui a classe model de negócio
 require_once 'Models/UsuariosModel.php';

 class UsuariosRotas{

    /**
     * Exibe a tela de Categoria
     * 
     */
    public function listarAction(){
        //Renderiza a página de Login
        $view = new Views(200,'Sistema/Admin/usuariosView');
        //Retorna para o navegador a página HTML à ser exibida.
        $view->imprimirHTML();
        
    }
    
 }
?>