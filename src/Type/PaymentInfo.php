<?php

namespace JouwWeb\DocData\Type;

/**
 * @author Tijs Verkoyen <php-docdatapayments@verkoyen.eu>
 */
class PaymentInfo extends AbstractObject
{
    /**
     * @var RiskCheck
     */
    protected $riskChecks;

    /**
     * @var AmexPaymentInfo
     */
    protected $amexPaymentInfo;

    /**
     * @var MasterCardPaymentInfo
     */
    protected $masterCardPaymentInfo;

    /**
     * @var VisaPaymentInfo
     */
    protected $visaPaymentInfo;

    /**
     * @var BankTransferPaymentInfo
     */
    protected $bankTransferPaymentInfo;

    /**
     * @var MaestroPaymentInfo
     */
    protected $maestroPaymentInfo;

    /**
     * @var MasterCardPaymentInfo
     */
    protected $misterCashPaymentInfo;

    /**
     * @var GiftCardPaymentInfo
     */
    protected $giftCardPaymentInfo;

    /**
     * @var IdealPaymentInfo
     */
    protected $iDealPaymentInfo;

    /**
     * @param AmexPaymentInfo $amexPaymentInfo
     */
    public function setAmexPaymentInfo($amexPaymentInfo)
    {
        $this->amexPaymentInfo = $amexPaymentInfo;
    }

    /**
     * @return AmexPaymentInfo
     */
    public function getAmexPaymentInfo()
    {
        return $this->amexPaymentInfo;
    }

    /**
     * @param BankTransferPaymentInfo $bankTransferPaymentInfo
     */
    public function setBankTransferPaymentInfo($bankTransferPaymentInfo)
    {
        $this->bankTransferPaymentInfo = $bankTransferPaymentInfo;
    }

    /**
     * @return BankTransferPaymentInfo
     */
    public function getBankTransferPaymentInfo()
    {
        return $this->bankTransferPaymentInfo;
    }

    /**
     * @param GiftCardPaymentInfo $giftCardPaymentInfo
     */
    public function setGiftCardPaymentInfo($giftCardPaymentInfo)
    {
        $this->giftCardPaymentInfo = $giftCardPaymentInfo;
    }

    /**
     * @return GiftCardPaymentInfo
     */
    public function getGiftCardPaymentInfo()
    {
        return $this->giftCardPaymentInfo;
    }

    /**
     * @param IdealPaymentInfo $iDealPaymentInfo
     */
    public function setIdealPaymentInfo($iDealPaymentInfo)
    {
        $this->iDealPaymentInfo = $iDealPaymentInfo;
    }

    /**
     * @return IdealPaymentInfo
     */
    public function getIdealPaymentInfo()
    {
        return $this->iDealPaymentInfo;
    }

    /**
     * @param MaestroPaymentInfo $maestroPaymentInfo
     */
    public function setMaestroPaymentInfo($maestroPaymentInfo)
    {
        $this->maestroPaymentInfo = $maestroPaymentInfo;
    }

    /**
     * @return MaestroPaymentInfo
     */
    public function getMaestroPaymentInfo()
    {
        return $this->maestroPaymentInfo;
    }

    /**
     * @param MasterCardPaymentInfo $masterCardPaymentInfo
     */
    public function setMasterCardPaymentInfo($masterCardPaymentInfo)
    {
        $this->masterCardPaymentInfo = $masterCardPaymentInfo;
    }

    /**
     * @return MasterCardPaymentInfo
     */
    public function getMasterCardPaymentInfo()
    {
        return $this->masterCardPaymentInfo;
    }

    /**
     * @param MasterCardPaymentInfo $misterCashPaymentInfo
     */
    public function setMisterCashPaymentInfo($misterCashPaymentInfo)
    {
        $this->misterCashPaymentInfo = $misterCashPaymentInfo;
    }

    /**
     * @return MasterCardPaymentInfo
     */
    public function getMisterCashPaymentInfo()
    {
        return $this->misterCashPaymentInfo;
    }

    /**
     * @param RiskCheck $riskChecks
     */
    public function setRiskChecks($riskChecks)
    {
        $this->riskChecks = $riskChecks;
    }

    /**
     * @return RiskCheck
     */
    public function getRiskChecks()
    {
        return $this->riskChecks;
    }

    /**
     * @param VisaPaymentInfo $visaPaymentInfo
     */
    public function setVisaPaymentInfo($visaPaymentInfo)
    {
        $this->visaPaymentInfo = $visaPaymentInfo;
    }

    /**
     * @return VisaPaymentInfo
     */
    public function getVisaPaymentInfo()
    {
        return $this->visaPaymentInfo;
    }
}
