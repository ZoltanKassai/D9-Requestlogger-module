<?php

namespace Drupal\requestlogger\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use \Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listens to the requests.
 */
class RequestLoggerSubscriber implements EventSubscriberInterface {

  /**
   * The requestlogger channel.
   *
   * @var LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs the RequestLoggerSubscriber.
   *
   * @param LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('requestlogger');
  }

  /**
   * Logs on every response.
   *
   * @param ResponseEvent $event
   *   The event to process.
   */
  public function onRespond(ResponseEvent $event) {
    $response = $event->getResponse();
    $base_path = base_path();
    $base_path_text = t("Base path: $base_path");
    if (!$response instanceof HtmlResponse) {
      return;
    }
    $attachments = $response->getAttachments();
    foreach ($attachments as $attachment) {
      if (isset($attachment['path']['currentPath'])) {
        $attachments_path = $attachment['path']['currentPath'];
        $attachments_path_text = t("Path: $attachments_path");
        $url_object = \Drupal::service('path.validator')->getUrlIfValid($attachments_path);
        $route_name = $url_object->getRouteName();
        $route_name_text = t("Route name: $route_name");
        \Drupal::logger('requestlogger')->notice("$base_path_text, $attachments_path_text, $route_name_text");
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond', 3];
    return $events;
  }

}
