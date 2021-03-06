<?php

namespace JouwWeb\DocData\Type;


class Name extends AbstractObject
{
    /**
     * For example: Mr., Mrs., Ms., Dr. This field is commonly omitted.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The initials.
     *
     * @var string
     */
    protected $initials;

    /**
     * The first given name.
     *
     * @var string
     */
    protected $first;

    /**
     * Any subsequent given name or names. May also be used as middle initial.
     *
     * @var string
     */
    protected $middle;

    /**
     * The family or inherited name(s).
     *
     * @var string
     */
    protected $last;

    /**
     * For example: Ph.D., Jr. (Junior), 3rd, Esq. (Exquire). This field is
     * commonly omitted.
     *
     * @var string
     */
    protected $suffix;

    /**
     * @param string $first
     */
    public function setFirst($first)
    {
        $this->first = $first;
    }

    /**
     * @return string
     */
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * @param string $initials
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;
    }

    /**
     * @return string
     */
    public function getInitials()
    {
        return $this->initials;
    }

    /**
     * @param string $last
     */
    public function setLast($last)
    {
        $this->last = $last;
    }

    /**
     * @return string
     */
    public function getLast()
    {
        return $this->last;
    }

    /**
     * @param string $middle
     */
    public function setMiddle($middle)
    {
        $this->middle = $middle;
    }

    /**
     * @return string
     */
    public function getMiddle()
    {
        return $this->middle;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
}
