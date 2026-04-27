-- Script para criar o banco de dados e a tabela de inscrições
-- Execute isso no seu phpMyAdmin (http://localhost/phpmyadmin)

CREATE DATABASE IF NOT EXISTS `copa_jiujitsu` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `copa_jiujitsu`;

CREATE TABLE IF NOT EXISTS `cop_inscricoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `cpf` varchar(20) NOT NULL,
  `faixa` varchar(50) NOT NULL,
  `equipe` varchar(255) NOT NULL,
  `plano` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status_pagamento` enum('pendente','pago','cancelado') DEFAULT 'pendente',
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
