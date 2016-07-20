<?php

namespace Bank;


interface Persistence
{
    public function persist(Activity $activity);
}