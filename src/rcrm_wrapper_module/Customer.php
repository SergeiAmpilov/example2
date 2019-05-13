<?php

namespace RcrmWrapperModule;

use RetailCrm\ApiClient;

class Customer
{
    /**
     * @var ApiClient $api
     */
    private $api;

    private $webasystId;
    private $retailId;
    private $firstName;
    private $lastName;
    private $email;
    private $phone;

    /**
     * Customer constructor.
     * @param ApiClient $api
     * @param array $customer
     */
    public function __construct($api, $customer)
    {
        $this->api = $api;
        $this->webasystId = $customer['id'];
        $this->setFirstName($customer['name']);
        $this->setLastName($customer['name']);
        $this->email = $customer['email'];
        $this->phone = $customer['phone'];
    }

    public function setFirstName($name)
    {
        $nameArr = explode(' ', $name);
        $this->firstName = $nameArr[0];
    }

    public function setLastName($name)
    {
        $nameArr = explode(' ', $name);
        $this->lastName = isset($nameArr[1]) ? $nameArr[1] : '';
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getRetailId()
    {
        return $this->retailId;
    }

    public function processCustomer()
    {
        try {

            $customer = $this->searchCustomer();

            if ($customer != null) {

                $this->retailId = $customer['id'];
                $this->updateCustomer();
                return $this->retailId;

            } else {

                $this->createCustomer();
                return $this->retailId;
            }

        } catch (\Exception $e) {
            throw new \Exception('Retail\Customer::processCustomer->' . $e->getMessage() . '->');
        }
    }

    private function searchCustomer()
    {

        $response = $this->api->request->customersGet('wa' . $this->webasystId, 'externalId');

        if (404 !== $response->getStatusCode()) {

            return $response['customer'];

        }

        $response = $this->api->request->customersList([
            'name' => $this->phone
        ]);

        $isRequestSuccessful = $response->isSuccessful() && 200 === $response->getStatusCode();

        if ($isRequestSuccessful) {
            foreach ($response['customers'] as $customer) if ($customer['externalId'] == $this->webasystId) {
                return $customer;
            }

        } else {
            throw new \Exception('Retail\Customer::searchCustomer->' . $response->getStatusCode() . '->');
        }


    }

    private function createCustomer()
    {

        $response = $this->api->request->customersCreate([
            'externalId' => 'wa' . $this->webasystId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phones' => [
                [
                    'number' => $this->phone
                ]
            ]
        ]);

        if (!($response->isSuccessful() && 201 === $response->getStatusCode())) {
            throw new \Exception('Retail\Customer::createCustomer->' . $response->getStatusCode() . '->');
        }
        $this->retailId = $response->id;

    }

    private function updateCustomer()
    {
        $response = $this->api->request->customersEdit([
            'id' => $this->retailId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phones' => [
                [
                    'number' => $this->phone
                ]
            ]
        ], 'id');

        if (!($response->isSuccessful() && 200 === $response->getStatusCode())) {
            throw new \Exception('Retail\Customer::updateCustomer->' . $response->getStatusCode() . '->');
        }
    }

}