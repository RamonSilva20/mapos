<?php
use Piggly\Pix\StaticPayload;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
   sarkozin
   https://github.com/sarcozin
*/

class Payment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->model('os_model');
        $this->load->model('mapos_model');
    }

    public function index($uuid = NULL)
    {
        if (!$uuid || !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid)) {
            $this->session->set_flashdata('error', 'Cobrança não encontrada');
            redirect('mapos');
        }

        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $this->load->database();

        $result = $this->os_model->getByPaymentId($uuid);

        if ($result) {
            $idOs = $result->idOs;

            $this->data['result'] = $result;
            $this->data['produtos'] = $this->os_model->getProdutos($idOs);
            $this->data['servicos'] = $this->os_model->getServicos($idOs);
            $this->data['emitente'] = $this->mapos_model->getEmitente();
            $this->data['comprovante'] = $this->os_model->getComprovanteByOsId($idOs);

            $pixKey = $this->db->get_where('configuracoes', ['config' => 'pix_key'])->row()->valor;
            $this->data['pix_key'] = $pixKey;
            $pixKey_type = $this->db->get_where('configuracoes', ['config' => 'pix_key_type'])->row()->valor;
            $this->data['pix_key_type'] = $pixKey_type;

            $totalServico = 0;
            $totalProdutos = 0;
            if ($return = $this->os_model->valorTotalOS($idOs)) {
                $totalServico = $return['totalServico'];
                $totalProdutos = $return['totalProdutos'];
                $this->data['totalServico'] = $totalServico;
                $this->data['totalProdutos'] = $totalProdutos;
            }

            $desconto = 0;
            $descontoFormatado = '0';
            if ($result->desconto > 0) {
                if ($result->tipo_desconto == 'porcento') {
                    $desconto = ($totalServico + $totalProdutos) * ($result->desconto / 100);
                    $descontoFormatado = $result->desconto . '%';
                } else {
                    $desconto = $result->desconto;
                    $descontoFormatado = 'R$ ' . number_format($result->desconto, 2, ',', '.');
                }
            }

            $valorFinal = ($totalServico + $totalProdutos) - $desconto;
            $this->data['subtotal'] = $totalServico + $totalProdutos;
            $this->data['desconto'] = $descontoFormatado;
            $this->data['valorFinal'] = $valorFinal;

            $pixPayload = $this->generatePixPayload($pixKey, $valorFinal, 'Pagamento OS ' . $result->idOs . ' ', $this->data['emitente']->nome, $this->data['emitente']->cidade, $pixKey_type);
            $qrCodeBase64 = $this->generateQrCodeBase64($pixPayload);

            $this->data['pix_qrcode_base64'] = $qrCodeBase64;
            $this->data['pix_payload'] = $pixPayload;

            $this->load->view('payment/payment', $this->data);
        } else {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }
    }



    private function generatePixPayload($pixKey, $amount, $description, $emitente, $cidade, $pix_key_type)
    {
        $payload = new StaticPayload();
        $payload->setPixKey($pix_key_type, $pixKey);
        $payload->setMerchantName($emitente);
        $payload->setMerchantCity($cidade);
        $payload->setAmount($amount);
        $payload->setTid($description);
        $payload->setDescription($description);

        return $payload->getPixCode();
    }

    private function generateQrCodeBase64($payload)
    {
        $qrCode = new QrCode($payload);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);

        $foregroundColor = new Color(0, 0, 0);
        $backgroundColor = new Color(255, 255, 255);
        $qrCode->setForegroundColor($foregroundColor);
        $qrCode->setBackgroundColor($backgroundColor);

        $logoPath = base_url() . 'assets/img/logo-for-pix.png';
        $logoWithBackground = $this->addBackgroundToLogo($logoPath, 50, 50, [255, 255, 255]);

        $tempLogoPath = sys_get_temp_dir() . '/logo_with_background.png';
        imagepng($logoWithBackground, $tempLogoPath);

        $logo = new Logo($tempLogoPath, 50, 50, false);

        $qrCodeWriter = new PngWriter();

        $qrCodeResult = $qrCodeWriter->write($qrCode, $logo);

        $dataUri = $qrCodeResult->getDataUri();

        list(, $base64) = explode(',', $dataUri);

        return $base64;
    }

    private function addBackgroundToLogo($logoPath, $width, $height, $backgroundColor)
    {
        $logoImage = imagecreatefrompng($logoPath);

        $newImage = imagecreatetruecolor($width, $height);
        $backgroundColorAllocated = imagecolorallocate($newImage, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
        imagefill($newImage, 0, 0, $backgroundColorAllocated);

        $logoWidth = imagesx($logoImage);
        $logoHeight = imagesy($logoImage);
        $x = ($width - $logoWidth) / 2;
        $y = ($height - $logoHeight) / 2;

        imagecopy($newImage, $logoImage, $x, $y, 0, 0, $logoWidth, $logoHeight);

        imagedestroy($logoImage);

        return $newImage;
    }

    public function details($uuid)
    {
        if (!$uuid || !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid)) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $this->load->database();

        $result = $this->os_model->getByPaymentId($uuid);

        if ($result) {
            $idOs = $result->idOs;

            $this->data['result'] = $result;
            $this->data['produtos'] = $this->os_model->getProdutos($idOs);
            $this->data['servicos'] = $this->os_model->getServicos($idOs);
            $this->data['emitente'] = $this->mapos_model->getEmitente();
            $this->data['anexos'] = $this->os_model->getAnexos($idOs);
            $this->data['anotacoes'] = $this->os_model->getAnotacoes($idOs);
            $this->data['editavel'] = $this->os_model->isEditable($idOs);

            $pixkey = $this->db->get_where('configuracoes', ['config' => 'pix_key'])->row_object()->valor;
            $this->data['pix_key'] = $pixkey;

            $totalServico = 0;
            $totalProdutos = 0;
            if ($return = $this->os_model->valorTotalOS($idOs)) {
                $totalServico = $return['totalServico'];
                $totalProdutos = $return['totalProdutos'];
                $this->data['totalServico'] = $totalServico;
                $this->data['totalProdutos'] = $totalProdutos;
            }

            $desconto = 0;
            $descontoFormatado = '0';
            if ($result->desconto > 0) {
                if ($result->tipo_desconto == 'porcento') {
                    $desconto = ($totalServico + $totalProdutos) * ($result->desconto / 100);
                    $descontoFormatado = $result->desconto . '%';
                } else {
                    $desconto = $result->desconto;
                    $descontoFormatado = 'R$ ' . number_format($result->desconto, 2, ',', '.');
                }
            }
            $valorFinal = ($totalServico + $totalProdutos) - $desconto;
            $this->data['subtotal'] = $totalServico + $totalProdutos;
            $this->data['desconto'] = $descontoFormatado;
            $this->data['valorFinal'] = $valorFinal;
            $this->load->view('payment/details', $this->data);
        } else {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }
    }

    public function upload_comprovante($os_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            show_404();
        }

        if (!isset($_FILES['comprovante']) || $_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Nenhum arquivo foi enviado ou ocorreu um erro durante o upload.']);
            die;
        }

        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $file_type = mime_content_type($_FILES['comprovante']['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de arquivo inválido. Apenas imagens (JPEG, PNG) e PDFs são permitidos.']);
            die;
        }

        $date = date('d-m-Y');
        $upload_path = './assets/arquivos/comprovantes/' . $date . '/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $file_name = time() . '_' . $_FILES['comprovante']['name'];
        $file_path = $upload_path . $file_name;

        if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $file_path)) {
            $data = [
                'os_id' => $os_id,
                'verified' => 0,
                'url_comprovante' => 'assets/arquivos/comprovantes/' . $date . '/' . $file_name
            ];
            $this->os_model->addComprovante($data);

            echo json_encode(['success' => true, 'message' => 'Comprovante enviado com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ocorreu um erro ao salvar o comprovante.']);
        }
        die;
    }
}
