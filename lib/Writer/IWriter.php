<?php

interface IWriter {
	public function save();
	public function download();
	public function set(World $world);
}