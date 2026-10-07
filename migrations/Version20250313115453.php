<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313115453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allocated_tree (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, tree_species_id INT NOT NULL, parcel_id INT NOT NULL, created_by_id INT DEFAULT NULL, track_code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_85547560A76ED395 (user_id), INDEX IDX_8554756049B22061 (tree_species_id), INDEX IDX_85547560465E670C (parcel_id), INDEX IDX_85547560B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_tree_species (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B55F769D989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_by_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_219CDA4AA76ED395 (user_id), INDEX IDX_219CDA4AB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, created_by_id INT DEFAULT NULL, label VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2D5B023498260155 (region_id), INDEX IDX_2D5B0234B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_import (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, customer_numbers VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, report_file_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2C8B124CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_import_user (customer_import_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_63F413C11D5F9BC3 (customer_import_id), INDEX IDX_63F413C1A76ED395 (user_id), PRIMARY KEY(customer_import_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE error_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gallery (id INT AUTO_INCREMENT NOT NULL, entity_type VARCHAR(255) NOT NULL, entity_id INT NOT NULL, caption VARCHAR(255) DEFAULT NULL, type VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, thumbnail_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE impact (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(50) NOT NULL, INDEX IDX_C409C00712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE impact_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, enum INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE impact_project (id INT AUTO_INCREMENT NOT NULL, project_child_id INT NOT NULL, impact_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, value DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_A6B9B8D2E02C49C0 (project_child_id), INDEX IDX_A6B9B8D2D128BC9B (impact_id), INDEX IDX_A6B9B8D2B03A8386 (created_by_id), INDEX IDX_A6B9B8D2896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE localisation (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(25) NOT NULL, region VARCHAR(50) NOT NULL, shape_id VARCHAR(50) NOT NULL, bounds JSON NOT NULL COMMENT \'(DC2Type:json)\', latitude NUMERIC(10, 6) NOT NULL, longitude NUMERIC(10, 6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parcel (id INT AUTO_INCREMENT NOT NULL, project_child_id INT NOT NULL, town_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, delimitation LONGTEXT NOT NULL, title VARCHAR(255) NOT NULL, tiffimage VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, planting_year DATETIME NOT NULL, city VARCHAR(255) DEFAULT NULL, latitude NUMERIC(16, 6) NOT NULL, longitude NUMERIC(16, 6) NOT NULL, banner_image VARCHAR(255) DEFAULT NULL, trees JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_C99B5D60D17F50A6 (uuid), INDEX IDX_C99B5D60E02C49C0 (project_child_id), INDEX IDX_C99B5D6075E23604 (town_id), INDEX IDX_C99B5D60B03A8386 (created_by_id), INDEX IDX_C99B5D60896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parcel_tree_species (id INT AUTO_INCREMENT NOT NULL, parcel_id INT NOT NULL, tree_species_id INT NOT NULL, tree_number INT NOT NULL, available_tree_number INT NOT NULL, INDEX IDX_E768E561465E670C (parcel_id), INDEX IDX_E768E56149B22061 (tree_species_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(21) DEFAULT NULL, address VARCHAR(100) DEFAULT NULL, token VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_312B3E165E237E06 (name), UNIQUE INDEX UNIQ_312B3E16E7927C74 (email), UNIQUE INDEX UNIQ_312B3E165F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner_customer (id INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, partner_id INT NOT NULL, created_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_E2F808B36B3CA4B (id_user), INDEX IDX_E2F808B39393F8FE (partner_id), INDEX IDX_E2F808B3B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_8157AA0F77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_role (profile_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_E1A105FECCFA12B8 (profile_id), INDEX IDX_E1A105FED60322AC (role_id), PRIMARY KEY(profile_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_child (id INT AUTO_INCREMENT NOT NULL, project_parent_id INT NOT NULL, partner_id INT NOT NULL, user_id INT NOT NULL, project_step_id INT NOT NULL, town_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, history LONGTEXT DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, banner_image VARCHAR(255) DEFAULT NULL, start_date DATETIME NOT NULL, environmental_impact_planted_trees VARCHAR(255) DEFAULT NULL, environmental_impact_co2 VARCHAR(255) DEFAULT NULL, economic_impact_sustainable_employment VARCHAR(255) DEFAULT NULL, economic_impact_restored_hectares VARCHAR(255) DEFAULT NULL, social_impact_seasonal_jobs VARCHAR(255) DEFAULT NULL, social_impact_indirect_beneficiaries VARCHAR(255) DEFAULT NULL, latitude NUMERIC(16, 6) NOT NULL, longitude NUMERIC(16, 6) NOT NULL, send_notifications TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_31E65DB7D17F50A6 (uuid), INDEX IDX_31E65DB763468FA7 (project_parent_id), INDEX IDX_31E65DB79393F8FE (partner_id), INDEX IDX_31E65DB7A76ED395 (user_id), INDEX IDX_31E65DB7E5AC12F9 (project_step_id), INDEX IDX_31E65DB775E23604 (town_id), INDEX IDX_31E65DB7B03A8386 (created_by_id), INDEX IDX_31E65DB7896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_parent (id INT AUTO_INCREMENT NOT NULL, project_type_id INT DEFAULT NULL, project_status_id INT DEFAULT NULL, localisation_id INT DEFAULT NULL, region_id INT NOT NULL, town_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, city TINYTEXT DEFAULT NULL, start_date DATETIME NOT NULL, history LONGTEXT DEFAULT NULL, banner_image VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_2A2A8B05D17F50A6 (uuid), INDEX IDX_2A2A8B05535280F6 (project_type_id), INDEX IDX_2A2A8B057ACB456A (project_status_id), INDEX IDX_2A2A8B05C68BE09C (localisation_id), INDEX IDX_2A2A8B0598260155 (region_id), INDEX IDX_2A2A8B0575E23604 (town_id), INDEX IDX_2A2A8B05B03A8386 (created_by_id), INDEX IDX_2A2A8B05896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_status (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_6CA48E56EA750E8 (label), UNIQUE INDEX UNIQ_6CA48E56989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_step (id INT AUTO_INCREMENT NOT NULL, project_step_name VARCHAR(255) NOT NULL, project_step_order VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_type (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, label VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, color VARCHAR(25) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_B54F9F31EA750E8 (label), UNIQUE INDEX UNIQ_B54F9F31989D9B62 (slug), UNIQUE INDEX UNIQ_B54F9F31D17F50A6 (uuid), INDEX IDX_B54F9F31B03A8386 (created_by_id), INDEX IDX_B54F9F31896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, label VARCHAR(255) NOT NULL, shape_id VARCHAR(50) NOT NULL, bounds JSON NOT NULL COMMENT \'(DC2Type:json)\', longitude DOUBLE PRECISION NOT NULL, latitude DOUBLE PRECISION NOT NULL, INDEX IDX_F62F176F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE report_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_by_id INT DEFAULT NULL, access_code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_47230D4BA76ED395 (user_id), INDEX IDX_47230D4BB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource_type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, resource_table_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_57698A6A77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE siementry (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, error_type_id INT DEFAULT NULL, event_type_id INT DEFAULT NULL, resource_type_id INT DEFAULT NULL, event_time VARCHAR(255) NOT NULL, source_addr VARCHAR(255) NOT NULL, event_result INT NOT NULL, additional_info VARCHAR(255) DEFAULT NULL, target_source_id INT NOT NULL, INDEX IDX_60644585A76ED395 (user_id), INDEX IDX_606445855808DDBF (error_type_id), INDEX IDX_60644585401B253C (event_type_id), INDEX IDX_6064458598EC6B7B (resource_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE story_telling (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, entity_type VARCHAR(255) NOT NULL, entity_id INT NOT NULL, title VARCHAR(255) NOT NULL, banner_image VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_CC12B5A7D17F50A6 (uuid), INDEX IDX_CC12B5A7B03A8386 (created_by_id), INDEX IDX_CC12B5A7896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_project (id INT AUTO_INCREMENT NOT NULL, town_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, entity_type VARCHAR(255) NOT NULL, entity_id INT NOT NULL, name VARCHAR(255) NOT NULL, banner VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, city VARCHAR(255) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, history_excerpt VARCHAR(255) NOT NULL, history_content LONGTEXT NOT NULL, latitude NUMERIC(10, 6) NOT NULL, longitude NUMERIC(10, 6) NOT NULL, created_at DATETIME NOT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_506B8C1AD17F50A6 (uuid), INDEX IDX_506B8C1A75E23604 (town_id), INDEX IDX_506B8C1AB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tree_species (id INT AUTO_INCREMENT NOT NULL, usage_type_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, common_name VARCHAR(255) NOT NULL, scientific_name VARCHAR(255) NOT NULL, track_code VARCHAR(255) NOT NULL, banner VARCHAR(255) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6F88550FD17F50A6 (uuid), INDEX IDX_6F88550F9BC8FA8C (usage_type_id), INDEX IDX_6F88550FB03A8386 (created_by_id), INDEX IDX_6F88550F896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usage_type (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_27744532D17F50A6 (uuid), INDEX IDX_27744532B03A8386 (created_by_id), INDEX IDX_27744532896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, profile_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, banner_image VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), INDEX IDX_8D93D649CCFA12B8 (profile_id), INDEX IDX_8D93D649B03A8386 (created_by_id), INDEX IDX_8D93D649896DBBDE (updated_by_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE allocated_tree ADD CONSTRAINT FK_85547560A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE allocated_tree ADD CONSTRAINT FK_8554756049B22061 FOREIGN KEY (tree_species_id) REFERENCES tree_species (id)');
        $this->addSql('ALTER TABLE allocated_tree ADD CONSTRAINT FK_85547560465E670C FOREIGN KEY (parcel_id) REFERENCES parcel (id)');
        $this->addSql('ALTER TABLE allocated_tree ADD CONSTRAINT FK_85547560B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B023498260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_import ADD CONSTRAINT FK_2C8B124CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_import_user ADD CONSTRAINT FK_63F413C11D5F9BC3 FOREIGN KEY (customer_import_id) REFERENCES customer_import (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_import_user ADD CONSTRAINT FK_63F413C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE impact ADD CONSTRAINT FK_C409C00712469DE2 FOREIGN KEY (category_id) REFERENCES impact_category (id)');
        $this->addSql('ALTER TABLE impact_project ADD CONSTRAINT FK_A6B9B8D2E02C49C0 FOREIGN KEY (project_child_id) REFERENCES project_child (id)');
        $this->addSql('ALTER TABLE impact_project ADD CONSTRAINT FK_A6B9B8D2D128BC9B FOREIGN KEY (impact_id) REFERENCES impact (id)');
        $this->addSql('ALTER TABLE impact_project ADD CONSTRAINT FK_A6B9B8D2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE impact_project ADD CONSTRAINT FK_A6B9B8D2896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE parcel ADD CONSTRAINT FK_C99B5D60E02C49C0 FOREIGN KEY (project_child_id) REFERENCES project_child (id)');
        $this->addSql('ALTER TABLE parcel ADD CONSTRAINT FK_C99B5D6075E23604 FOREIGN KEY (town_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE parcel ADD CONSTRAINT FK_C99B5D60B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE parcel ADD CONSTRAINT FK_C99B5D60896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE parcel_tree_species ADD CONSTRAINT FK_E768E561465E670C FOREIGN KEY (parcel_id) REFERENCES parcel (id)');
        $this->addSql('ALTER TABLE parcel_tree_species ADD CONSTRAINT FK_E768E56149B22061 FOREIGN KEY (tree_species_id) REFERENCES tree_species (id)');
        $this->addSql('ALTER TABLE partner_customer ADD CONSTRAINT FK_E2F808B36B3CA4B FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE partner_customer ADD CONSTRAINT FK_E2F808B39393F8FE FOREIGN KEY (partner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE partner_customer ADD CONSTRAINT FK_E2F808B3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FECCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FED60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB763468FA7 FOREIGN KEY (project_parent_id) REFERENCES project_parent (id)');
        $this->addSql('ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB79393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id)');
        $this->addSql('ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB7E5AC12F9 FOREIGN KEY (project_step_id) REFERENCES project_step (id)');
        $this->addSql('ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB775E23604 FOREIGN KEY (town_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_child ADD CONSTRAINT FK_31E65DB7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_parent ADD CONSTRAINT FK_2A2A8B05535280F6 FOREIGN KEY (project_type_id) REFERENCES project_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE project_parent ADD CONSTRAINT FK_2A2A8B057ACB456A FOREIGN KEY (project_status_id) REFERENCES project_status (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE project_parent ADD CONSTRAINT FK_2A2A8B05C68BE09C FOREIGN KEY (localisation_id) REFERENCES localisation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE project_parent ADD CONSTRAINT FK_2A2A8B0598260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE project_parent ADD CONSTRAINT FK_2A2A8B0575E23604 FOREIGN KEY (town_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE project_parent ADD CONSTRAINT FK_2A2A8B05B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_parent ADD CONSTRAINT FK_2A2A8B05896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_type ADD CONSTRAINT FK_B54F9F31B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_type ADD CONSTRAINT FK_B54F9F31896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE report_history ADD CONSTRAINT FK_47230D4BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report_history ADD CONSTRAINT FK_47230D4BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE siementry ADD CONSTRAINT FK_60644585A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE siementry ADD CONSTRAINT FK_606445855808DDBF FOREIGN KEY (error_type_id) REFERENCES error_type (id)');
        $this->addSql('ALTER TABLE siementry ADD CONSTRAINT FK_60644585401B253C FOREIGN KEY (event_type_id) REFERENCES event_type (id)');
        $this->addSql('ALTER TABLE siementry ADD CONSTRAINT FK_6064458598EC6B7B FOREIGN KEY (resource_type_id) REFERENCES resource_type (id)');
        $this->addSql('ALTER TABLE story_telling ADD CONSTRAINT FK_CC12B5A7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE story_telling ADD CONSTRAINT FK_CC12B5A7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sub_project ADD CONSTRAINT FK_506B8C1A75E23604 FOREIGN KEY (town_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE sub_project ADD CONSTRAINT FK_506B8C1AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tree_species ADD CONSTRAINT FK_6F88550F9BC8FA8C FOREIGN KEY (usage_type_id) REFERENCES usage_type (id)');
        $this->addSql('ALTER TABLE tree_species ADD CONSTRAINT FK_6F88550FB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tree_species ADD CONSTRAINT FK_6F88550F896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE usage_type ADD CONSTRAINT FK_27744532B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE usage_type ADD CONSTRAINT FK_27744532896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allocated_tree DROP FOREIGN KEY FK_85547560A76ED395');
        $this->addSql('ALTER TABLE allocated_tree DROP FOREIGN KEY FK_8554756049B22061');
        $this->addSql('ALTER TABLE allocated_tree DROP FOREIGN KEY FK_85547560465E670C');
        $this->addSql('ALTER TABLE allocated_tree DROP FOREIGN KEY FK_85547560B03A8386');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AB03A8386');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B023498260155');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234B03A8386');
        $this->addSql('ALTER TABLE customer_import DROP FOREIGN KEY FK_2C8B124CB03A8386');
        $this->addSql('ALTER TABLE customer_import_user DROP FOREIGN KEY FK_63F413C11D5F9BC3');
        $this->addSql('ALTER TABLE customer_import_user DROP FOREIGN KEY FK_63F413C1A76ED395');
        $this->addSql('ALTER TABLE impact DROP FOREIGN KEY FK_C409C00712469DE2');
        $this->addSql('ALTER TABLE impact_project DROP FOREIGN KEY FK_A6B9B8D2E02C49C0');
        $this->addSql('ALTER TABLE impact_project DROP FOREIGN KEY FK_A6B9B8D2D128BC9B');
        $this->addSql('ALTER TABLE impact_project DROP FOREIGN KEY FK_A6B9B8D2B03A8386');
        $this->addSql('ALTER TABLE impact_project DROP FOREIGN KEY FK_A6B9B8D2896DBBDE');
        $this->addSql('ALTER TABLE parcel DROP FOREIGN KEY FK_C99B5D60E02C49C0');
        $this->addSql('ALTER TABLE parcel DROP FOREIGN KEY FK_C99B5D6075E23604');
        $this->addSql('ALTER TABLE parcel DROP FOREIGN KEY FK_C99B5D60B03A8386');
        $this->addSql('ALTER TABLE parcel DROP FOREIGN KEY FK_C99B5D60896DBBDE');
        $this->addSql('ALTER TABLE parcel_tree_species DROP FOREIGN KEY FK_E768E561465E670C');
        $this->addSql('ALTER TABLE parcel_tree_species DROP FOREIGN KEY FK_E768E56149B22061');
        $this->addSql('ALTER TABLE partner_customer DROP FOREIGN KEY FK_E2F808B36B3CA4B');
        $this->addSql('ALTER TABLE partner_customer DROP FOREIGN KEY FK_E2F808B39393F8FE');
        $this->addSql('ALTER TABLE partner_customer DROP FOREIGN KEY FK_E2F808B3B03A8386');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FECCFA12B8');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FED60322AC');
        $this->addSql('ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB763468FA7');
        $this->addSql('ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB79393F8FE');
        $this->addSql('ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB7A76ED395');
        $this->addSql('ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB7E5AC12F9');
        $this->addSql('ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB775E23604');
        $this->addSql('ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB7B03A8386');
        $this->addSql('ALTER TABLE project_child DROP FOREIGN KEY FK_31E65DB7896DBBDE');
        $this->addSql('ALTER TABLE project_parent DROP FOREIGN KEY FK_2A2A8B05535280F6');
        $this->addSql('ALTER TABLE project_parent DROP FOREIGN KEY FK_2A2A8B057ACB456A');
        $this->addSql('ALTER TABLE project_parent DROP FOREIGN KEY FK_2A2A8B05C68BE09C');
        $this->addSql('ALTER TABLE project_parent DROP FOREIGN KEY FK_2A2A8B0598260155');
        $this->addSql('ALTER TABLE project_parent DROP FOREIGN KEY FK_2A2A8B0575E23604');
        $this->addSql('ALTER TABLE project_parent DROP FOREIGN KEY FK_2A2A8B05B03A8386');
        $this->addSql('ALTER TABLE project_parent DROP FOREIGN KEY FK_2A2A8B05896DBBDE');
        $this->addSql('ALTER TABLE project_type DROP FOREIGN KEY FK_B54F9F31B03A8386');
        $this->addSql('ALTER TABLE project_type DROP FOREIGN KEY FK_B54F9F31896DBBDE');
        $this->addSql('ALTER TABLE region DROP FOREIGN KEY FK_F62F176F92F3E70');
        $this->addSql('ALTER TABLE report_history DROP FOREIGN KEY FK_47230D4BA76ED395');
        $this->addSql('ALTER TABLE report_history DROP FOREIGN KEY FK_47230D4BB03A8386');
        $this->addSql('ALTER TABLE siementry DROP FOREIGN KEY FK_60644585A76ED395');
        $this->addSql('ALTER TABLE siementry DROP FOREIGN KEY FK_606445855808DDBF');
        $this->addSql('ALTER TABLE siementry DROP FOREIGN KEY FK_60644585401B253C');
        $this->addSql('ALTER TABLE siementry DROP FOREIGN KEY FK_6064458598EC6B7B');
        $this->addSql('ALTER TABLE story_telling DROP FOREIGN KEY FK_CC12B5A7B03A8386');
        $this->addSql('ALTER TABLE story_telling DROP FOREIGN KEY FK_CC12B5A7896DBBDE');
        $this->addSql('ALTER TABLE sub_project DROP FOREIGN KEY FK_506B8C1A75E23604');
        $this->addSql('ALTER TABLE sub_project DROP FOREIGN KEY FK_506B8C1AB03A8386');
        $this->addSql('ALTER TABLE tree_species DROP FOREIGN KEY FK_6F88550F9BC8FA8C');
        $this->addSql('ALTER TABLE tree_species DROP FOREIGN KEY FK_6F88550FB03A8386');
        $this->addSql('ALTER TABLE tree_species DROP FOREIGN KEY FK_6F88550F896DBBDE');
        $this->addSql('ALTER TABLE usage_type DROP FOREIGN KEY FK_27744532B03A8386');
        $this->addSql('ALTER TABLE usage_type DROP FOREIGN KEY FK_27744532896DBBDE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CCFA12B8');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B03A8386');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('DROP TABLE allocated_tree');
        $this->addSql('DROP TABLE category_tree_species');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE customer_import');
        $this->addSql('DROP TABLE customer_import_user');
        $this->addSql('DROP TABLE error_type');
        $this->addSql('DROP TABLE event_type');
        $this->addSql('DROP TABLE gallery');
        $this->addSql('DROP TABLE impact');
        $this->addSql('DROP TABLE impact_category');
        $this->addSql('DROP TABLE impact_project');
        $this->addSql('DROP TABLE localisation');
        $this->addSql('DROP TABLE parcel');
        $this->addSql('DROP TABLE parcel_tree_species');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE partner_customer');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE profile_role');
        $this->addSql('DROP TABLE project_child');
        $this->addSql('DROP TABLE project_parent');
        $this->addSql('DROP TABLE project_status');
        $this->addSql('DROP TABLE project_step');
        $this->addSql('DROP TABLE project_type');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE report_history');
        $this->addSql('DROP TABLE resource_type');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE siementry');
        $this->addSql('DROP TABLE story_telling');
        $this->addSql('DROP TABLE sub_project');
        $this->addSql('DROP TABLE tree_species');
        $this->addSql('DROP TABLE usage_type');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
