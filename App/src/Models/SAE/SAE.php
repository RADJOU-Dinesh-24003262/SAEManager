<?php

/**
 * Represents a sae in the system.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\SAE
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */

class SAE {

    private int $sae_subject_id;
    private int $responsible_prof_id;
    private int $client_id;
    private string $subject_name;
    private string $begin_date;
    private string $end_date;

    function __construct(Array $data) {
        $sae_subject_id = $data[0];
        $responsible_prof_id = $data[1];
        $client_id = $data[2];
        $subject_name = $data[3];
        $begin_date = $data[4];
        $end_date = $data[5];
    }

    public function createSAEsFromArray(Array $saes):Array{
        $result = [];
        foreach ($saes as $sae) {
            $result[] = new SAE($sae);
    }
    return $result;
    }

    public function getDataArray():Array{
        return[
            $this->sae_subject_id,
            $this->responsible_prof_id,
            $this->client_id,
            $this->subject_name,
            $this->begin_date,
            $this->end_date
        ];
    }


    // ---------------------
    // Getters and Setters
    // ---------------------

    public function getSaeSubjectId(): int
    {
        return $this->sae_subject_id;
    }

    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }

    public function getResponsibleProfId(): int
    {
        return $this->responsible_prof_id;
    }

    public function setResponsibleProfId(int $responsible_prof_id): void
    {
        $this->responsible_prof_id = $responsible_prof_id;
    }

    public function getClientId(): int
    {
        return $this->client_id;
    }

    public function setClientId(int $client_id): void
    {
        $this->client_id = $client_id;
    }

    public function getSubjectName(): string
    {
        return $this->subject_name;
    }

    public function setSubjectName(string $subject_name): void
    {
        $this->subject_name = $subject_name;
    }

    public function getBeginDate(): string
    {
        return $this->begin_date;
    }

    public function setBeginDate(string $begin_date): void
    {
        $this->begin_date = $begin_date;
    }

    public function getEndDate(): string
    {
        return $this->end_date;
    }

    public function setEndDate(string $end_date): void
    {
        $this->end_date = $end_date;
    }

}