<?php

namespace Models\Images;

/**
 * Класс для обработки новостных изображений.
 */
class ImageHandler {


    /** Сообщение о неизвестном изображении */
    const UNKNOWN_IMAGE_MESSAGE = "Unknown image!";
    /** Сообщение о неизвестном формате изображения */
    const UNKNOWN_IMAGE_FORMAT_MESSAGE = "Unknown image format!";
    /** Тип изображения на выходе */
    const IMAGE_TYPE = ".jpg";

    /** @var - изображение для обработки (изменения размера) */
    private $image;
    /** @var string - расширение файла изображения */
    private $fileExtension;
    /** @var string - каталог с обработанными изображениями */
    private $destinationDirectory;
    /** @var int - длина имени генерируемого названия изображения */
    private $fileNameLength;

    /**
     * ImageHandler constructor.
     * @param $imageUrl - Адрес изображения.
     * @param $destinationDirectory - Путь к каталогу для сохранения изображения.
     * @param $fileNameLength - Длина имени файла.
     * @throws \Exception - Исключение в случае ошибки при загрузке изображения.
     */
    public function __construct($imageUrl, $destinationDirectory, $fileNameLength = 8) {
        try {
            $this->fileExtension = strtolower(end(explode('.', $imageUrl)));
            $this->fileNameLength = $fileNameLength;
            $this->destinationDirectory = $destinationDirectory;
            $this->loadImage($imageUrl);

            if ($this->image === false)
                throw new \Exception(self::UNKNOWN_IMAGE_MESSAGE);
        } catch (\ErrorException $e) {
            throw new \Exception(self::UNKNOWN_IMAGE_FORMAT_MESSAGE);
        }
    }

    /**
     * Сохранить изображение с указанной шириной и сжатием.
     * @param $newWidth - Конечная ширина изображения.
     * @param int $jpegCompression - Сжатие Jpeg-файлов.
     * @return string - Имя сохраненного файла.
     */
    public function saveResizedImage($newWidth, $jpegCompression = 75) {
        $imageFullName = $this->generateFileName();
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        $ratio = $height / $width;
        $resultImage = imagecreatetruecolor($newWidth, $newWidth * $ratio);
        imagecopyresampled($resultImage, $this->image, 0, 0, 0, 0,
            $newWidth, $newWidth * $ratio, $width, $height);

        imagejpeg($resultImage, $imageFullName, $jpegCompression);

        return $imageFullName;
    }


    /**
     * Сгенерировать случайное неповторяющееся (в указанном каталоге) имя файла заданной (в конструкторе) длины.
     * Тип конечного файла - "jpg".
     * @return string - Имя файла.
     */
    private function generateFileName() {
        $randomName = substr(md5(uniqid()), 0, $this->fileNameLength) . self::IMAGE_TYPE;
        while (file_exists($this->destinationDirectory . $randomName)) {
            $randomName = substr(md5(uniqid()), 0, $this->fileNameLength) . self::IMAGE_TYPE;
        }

        return $this->destinationDirectory . $randomName;
    }

    /**
     * Загрузить изображение в зависимости от его типа
     * @param $imageUrl - Источник изображения
     * @throws \Exception - Исключение в случае неизвестного формата изображения
     */
    private function loadImage($imageUrl) {
        if ('jpg' == $this->fileExtension || 'jpeg' == $this->fileExtension) {
            $this->image = imagecreatefromjpeg($imageUrl);
        } elseif ('gif' == $this->fileExtension) {
            $this->image = imagecreatefromgif($imageUrl);
        } elseif ('png' == $this->fileExtension) {
            $this->image = imagecreatefrompng($imageUrl);
        } else {
            throw new \Exception(self::UNKNOWN_IMAGE_FORMAT_MESSAGE);
        }
    }


}