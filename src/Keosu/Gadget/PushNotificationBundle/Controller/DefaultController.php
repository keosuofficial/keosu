<?php

namespace Keosu\Gadget\PushNotificationBundle\Controller;

use Keosu\Gadget\PushNotificationBundle\Entity\Devices;

use RMS\PushNotificationsBundle\Message\AndroidMessage;
use RMS\PushNotificationsBundle\Message\AppleMessage;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
	public function sendPushNotificationAction()
	{
		$em = $this->get('doctrine')->getManager();
		$request = $this->get('request');
		$error = '';$valid = false;

		$formBuilder = $this->createFormBuilder();
		$formBuilder->add('message', 'textarea', array(
						'attr' => array('maxlength' => 64)
		));
		$form = $formBuilder->getForm();

		if($request->getMethod() == 'POST') {
			$form->bind($request);
			if($form->isValid()) {
				try {
					$pushService = $this->container->get('keosu.plugin.push.notification.service');
					$devices = $em->getRepository('KeosuGadgetPushNotificationBundle:Devices');
					$androidMessage = new AndroidMessage();
					$androidMessage->setGCM(true);
					$appleMessage = new AppleMessage();
					foreach($devices as $device) {
						if($device->getType() === Devices::TYPE_APPLE) {
							$appleMessage->setDeviceIdentifier($device->getToken());
							//$pushService->send($appleMessage);
						} else if($device->getType() === Devices::TYPE_ANDROID) {
							$androidMessage->addGCMIdentifier($device->getToken());
						}
					}
					$pushService->send($androidMessage);
					$valid = true;
				} catch(\Exception $e) {
					$error = $e->getMessage();
				}
			}
		}
		return $this->render('KeosuGadgetPushNotificationBundle:Default:index.html.twig', array(
									'form' => $form->createView(),
									'error' => $error,
									'valid' => $valid
							));
	}
}
