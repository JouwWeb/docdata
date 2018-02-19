<?php

namespace JouwWeb\DocData\Type;


class StatusReport extends AbstractObject
{
    /**
     * @var ApproximateTotals
     */
    protected $approximateTotals;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @param ApproximateTotals $approximateTotals
     */
    public function setApproximateTotals($approximateTotals)
    {
        $this->approximateTotals = $approximateTotals;
    }

    /**
     * @return ApproximateTotals
     */
    public function getApproximateTotals()
    {
        return $this->approximateTotals;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
