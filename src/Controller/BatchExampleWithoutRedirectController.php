<?php

namespace Drupal\batch_example_without_redirect\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\BaseCommand;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller to provide demo of batch example without redirection.
 *
 * Update node data controller to update the node data by
 * ajax rather than redirect to core batch page.
 */
class BatchExampleWithoutRedirectController extends ControllerBase {

  /**
   * Function to update the node data.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   * @param string $nojs
   *   Nojs use to check request by ajax or not.
   */
  public function updateNodeData(Request $request, $nojs = 'nojs') {
    $method = $request->server->get('REQUEST_METHOD');
    $ajaxResponse = new AjaxResponse();
    // Add logic to check whether request to batch controller by
    // post method or not so that batch process not redirect to batch
    // window itself (i.e. by default core functionality)
    if ($nojs == 'ajax' && $method == 'POST') {
      // Get user submit values.
      $all_values = $request->request->all();
      $fields = $all_values['fields'];

      $node_storage = $this->entityTypeManager()->getStorage('node');
      $query = $node_storage->getQuery();

      $ids = $query->condition('type', $fields['content_type'])
        ->accessCheck(FALSE)
        ->execute();

      $batch_builder = new BatchBuilder();
      $batch_builder
        ->setTitle($this->t('Updating Data ..'))
        ->setFinishCallback([
          '\Drupal\batch_example_without_redirect\NodeData',
          'exportFinished',
        ])
        ->setInitMessage($this->t('Updating Node.'))
        ->setErrorMessage($this->t('The process has encountered an error.'));

      $ids_chunk = array_chunk($ids, 100);
      foreach ($ids_chunk as $id) {
        $batch_builder->addOperation([
          '\Drupal\batch_example_without_redirect\NodeData',
          'processNodeData',
        ],
        [
          $id,
        ]);
      }

      batch_set($batch_builder->toArray());

      // Get batch.
      $batch =& batch_get();

      $batch['progressive'] = TRUE;

      // Create the batch_process() to initiate batch process.
      $response = batch_process();

      $batch_id = $batch['id'] ?? '0';

      // Return the response to the ajax output.
      return $ajaxResponse->addCommand(new BaseCommand($batch_id, $response->getTargetUrl()));

    }

    return $ajaxResponse;

  }

}
