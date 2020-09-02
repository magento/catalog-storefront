# Overview

Declare DTO which represent the message from Export API with notification about changed catalog entity

This is ad-hoc solution:
Due to both parts (Export API and Message Broker) use the Magento Queue framework which requires to have "etc/communication.xml" for declaration input/output format for the message in the queue we have only 3 options to run tests in monolith installation (for local purpose or CICD):
- identical namespace: have the same DTO name used in communication.xml in both parts (current approach)
- use "string" as a message type and encode/decode message manually with \Magento\Framework\Webapi\ServiceInputProcessor::convertValue
- use 3d-party library on client side (Message Broker) to work with message bus




