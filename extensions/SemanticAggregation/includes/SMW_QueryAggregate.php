<?php
abstract class SMWQueryAggregate {
	protected $embedded = true;
	public function isEmbedded() {
		return $this->embedded;
	}
	protected $show = true;
	public function show() {
		return $this->show;
	}
	public function getResultPrefix($outputmode) {
		return '';
	}

	abstract public function appendValue($value);
	abstract public function getResult($outputmode);
}
class SMWFakeQueryAggregate extends SMWQueryAggregate {
	public function appendValue($value) {
	}
	public function getResult($outputmode) {
		return '';
	}
}
class SMWSumQueryAggregate extends SMWQueryAggregate {
	private $total = 0;
	private $isNumeric = false;
	private $object = NULL;

	private $mLinker;
	public function SMWSumQueryAggregate($linker, $pr = NULL){
		$this->mLinker = $linker;
	}
	public function appendValue($value) {
		if ($value->isNumeric()) {
			$this->total += $value->getWikiValue();
			$this->isNumeric = true;
			if(!$this->object) $this->object = clone $value;
		}
	}
	public function getResult($outputmode) {
		if($this->isNumeric) {
			$this->object->setUserValue('' . $this->total . $this->object->getUnit());
			return $this->object->getShortText($outputmode, $this->mLinker);
		} else {
			return 'N/A';
		}
	}
	public function getResultPrefix($outputmode) {
		return 'Sum : ';
	}
}
class SMWAverageQueryAggregate extends SMWQueryAggregate {
	private $total = 0;
	private $sum = 0;
	private $isNumeric = false;
	private $object = NULL;
	private $mLinker;
	public function SMWAverageQueryAggregate($linker, $pr = NULL){
		$this->mLinker = $linker;
	}
	public function appendValue($value) {
		if ($value->isNumeric()) {
			$this->total += $value->getWikiValue();
			$this->sum ++;
			$this->isNumeric = true;
			if(!$this->object) $this->object = clone $value;
		}
	}
	public function getResult($outputmode) {
		if(($this->isNumeric)&&($this->sum > 0)) {
			$this->object->setUserValue('' . ($this->total / $this->sum) . $this->object->getUnit());
			return $this->object->getShortText($outputmode, $this->mLinker);
		} else {
			return 'N/A';
		}
	}
	public function getResultPrefix($outputmode) {
		return 'Average : ';
	}
}

class SMWMaxQueryAggregate extends SMWQueryAggregate {
	private $max = 0;
	private $isNumeric = false;
	private $object = NULL;
	private $mLinker;
	public function SMWMaxQueryAggregate($linker, $pr = NULL){
		$this->mLinker = $linker;
	}
	public function appendValue($value) {
		if ($value->isNumeric()) {
			if(!$this->object) {
				$this->object = clone $value;
				$this->max = $value->getWikiValue();
			} else if($this->max < $value->getWikiValue()) {
				$this->max = $value->getWikiValue();
			}
			$this->isNumeric = true;
		}
	}
	public function getResult($outputmode) {
		if($this->isNumeric) {
			$this->object->setUserValue('' . $this->max . $this->object->getUnit());
			return $this->object->getShortText($outputmode, $this->mLinker);
		} else {
			return 'N/A';
		}
	}
	public function getResultPrefix($outputmode) {
		return 'Max : ';
	}
}

class SMWMinQueryAggregate extends SMWQueryAggregate {
	private $min = 0;
	private $isNumeric = false;
	private $object = NULL;
	private $mLinker;
	public function SMWMinQueryAggregate($linker, $pr = NULL){
		$this->mLinker = $linker;
	}
	public function appendValue($value) {
		if ($value->isNumeric()) {
			if(!$this->object) {
				$this->object = clone $value;
				$this->min = $value->getWikiValue();
			} else if($this->min > $value->getWikiValue()) {
				$this->min = $value->getWikiValue();
			}
			$this->isNumeric = true;
		}
	}
	public function getResult($outputmode) {
		if($this->isNumeric) {
			$this->object->setUserValue('' . $this->min . $this->object->getUnit());
			return $this->object->getShortText($outputmode, $this->mLinker);
		} else {
			return 'N/A';
		}
	}
	public function getResultPrefix($outputmode) {
		return 'Min : ';
	}
}

class SMWCountQueryAggregate extends SMWQueryAggregate {
	private $mCount = 0;
	public function SMWCountQueryAggregate($linker, $pr = NULL){
	}
	public function appendValue($value) {
		$this->mCount ++;
	}
	public function getResult($outputmode) {
		return $this->mCount;
	}
	public function getResultPrefix($outputmode) {
		return 'Count : ';
	}
}
