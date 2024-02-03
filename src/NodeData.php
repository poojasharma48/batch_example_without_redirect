<?php

namespace Drupal\batch_example_without_redirect;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\node\Entity\Node;

/**
 * Noda Data class to process node data in batch.
 */
class NodeData {

  /**
   * Function to procecss the node data in batch.
   */
  public static function processNodeData($nids, &$context) {
    $message = 'Updating Node...';

    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
    foreach ($nodes as $node) {
      $node = Node::load($node->id());
      $title = $node->getTitle();
      // Update node title, you can add any logic here as per need.
      $node->setTitle('Update ' . $title);
      $results[] = $node->save();
    }

    $context['message'] = $message;
    $context['results'] = $results;

  }

  /**
   * Function that executes post batch processing.
   */
  public static function exportFinished($success, $results, $operations) {

    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }

    \Drupal::messenger()->addMessage($message);

    $ajaxResponse = new AjaxResponse();

    return $ajaxResponse;

  }

}
