<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        $data['data'] = '';
        $this->load->view('home', $data, FALSE);
    }

    public function get_data_last_update()
    {
        $html = file_get_html('https://kawalcorona.com/');
        $content = $html->find('div.container div div div.col p');
        echo $content[0];
    }

   /* public function get_data_total()
    {
        $html = file_get_html('http://president.ac.id/p/148-news/s/16/8');
        // Find all images
        foreach($html->find('div.newsArchive') as $article) {
            $item['date']  = $article->find('h6', 0)->plaintext;
            $item['title'] = $article->find('h3', 0)->plaintext;
            $item['intro'] = $article->find('p', 0)->plaintext;
            $item['image'] = $article->find('img', 0)->src;
            $item['link']  = $article->find('a[class="btn  btn-red"]', 0)->href;
            $articles[]    = $item;
        }

        echo json_encode($articles);
    }*/

}