<?php

namespace WebSK\Logger\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WebSK\Entity\InterfaceEntity;
use WebSK\Entity\InterfaceEntityService;
use WebSK\Logger\CompareHTML;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Logger\LoggerConfig;
use WebSK\Utils\Sanitize;
use WebSK\Views\LayoutDTO;
use WebSK\Slim\RequestHandlers\BaseHandler;
use WebSK\Views\BreadcrumbItemDTO;
use WebSK\Logger\LoggerServiceProvider;
use WebSK\Views\PhpRender;

/**
 * Class EntryEditHandler
 * @package WebSK\Logger\RequestHandlers
 */
class EntryEditHandler extends BaseHandler
{

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $entry_id
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, int $entry_id): ResponseInterface
    {
        $entry_obj = LoggerServiceProvider::getEntryService($this->container)->getById($entry_id, false);
        if (!$entry_obj) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $html = '';
        $html .= $this->renderRecordHead($entry_id);

        try {
            $html .= $this->delta($entry_id);
            $html .= $this->renderObjectFields($entry_id);
        } catch (\Throwable $e) {
            $html .= '<div class="alert alert-danger">Структура объекта была изменена. Показ содержимого объекта невозможен.</div>';
        }

        $layout_dto = new LayoutDTO();
        $layout_dto->setTitle(date('Y.d.m H:i', $entry_obj->getCreatedAtTs()));
        $layout_dto->setContentHtml($html);
        $breadcrumbs_arr = [
            new BreadcrumbItemDTO(
                'Журналы',
                $this->urlFor(EntriesListHandler::class)
            ),
            new BreadcrumbItemDTO(
                $entry_obj->getObjectFullId(),
                $this->urlFor(
                    ObjectEntriesListHandler::class,
                    ['object_full_id' => urlencode($entry_obj->getObjectFullId())]
                )
            ),
        ];
        $layout_dto->setBreadcrumbsDtoArr($breadcrumbs_arr);

        return PhpRender::renderLayout($response, LoggerConfig::getAdminLayout(), $layout_dto);
    }

    /**
     * @param int $current_record_id
     * @return string
     */
    public function delta(int $current_record_id): string
    {
        $html = '';

        $current_record_obj = LoggerServiceProvider::getEntryService($this->container)->getById($current_record_id);

        $prev_record_id = LoggerServiceProvider::getEntryService($this->container)
            ->getPrevRecordEntryId($current_record_id);

        if (!$prev_record_id) {
            return '<div>Предыдущая запись истории для этого объекта не найдена.</div>';
        }

        $prev_record_obj = LoggerServiceProvider::getEntryService($this->container)->getById($prev_record_id);


        $edit_url = $this->urlFor(EntryEditHandler::class, ['entry_id' => $prev_record_id]);

        // определение дельты

        $html .= '<h2>Изменения относительно <a href="' . $edit_url . '">предыдущей версии</a></h2>';

        $current_obj = unserialize($current_record_obj->getSerializedObject());
        $prev_obj = unserialize($prev_record_obj->getSerializedObject());

        $current_record_as_list = $this->convertValueToList($current_obj);
        ksort($current_record_as_list); // сортируем для красоты
        $prev_record_as_list = $this->convertValueToList($prev_obj);
        ksort($prev_record_as_list); // сортируем для красоты

        $added_rows = array_diff_key($current_record_as_list, $prev_record_as_list);
        $deleted_rows = array_diff_key($prev_record_as_list, $current_record_as_list);

        if ($added_rows || $deleted_rows) {
            $html .= '<table class="table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>Поле</th>';
            $html .= '<th>Старое значение</th>';
            $html .= '<th>Новое значение</th>';
            $html .= '</tr>';
            $html .= '</thead>';

            foreach ($added_rows as $k => $v) {
                $html .= '<tr>';
                $html .= '<td><b>' . $k . '</b></td>';
                $html .= '<td style="background-color: #eee;"></td>';
                $html .= '<td>' . Sanitize::sanitizeTagContent($this->renderDeltaValue($v)) . '</td>';
                $html .= '</tr>';
            }

            foreach ($deleted_rows as $k => $v) {
                $html .= '<tr>';
                $html .= '<td><b>' . $k . '</b></td>';
                $html .= '<td>' . Sanitize::sanitizeTagContent($this->renderDeltaValue($v)) . '</td>';
                $html .= '<td style="background-color: #eee;"></td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
        }

        foreach ($current_record_as_list as $k => $current_v) {
            if (!array_key_exists($k, $prev_record_as_list)) {
                continue;
            }

            $prev_v = $prev_record_as_list[$k];
            if ($current_v == $prev_v) {
                continue;
            }

            $html .= CompareHTML::drawCompare($prev_v, $current_v, $k);
        }

        return $html;
    }

    /**
     * @param $v
     * @return string
     */
    protected function renderDeltaValue($v): string
    {
        $limit = 300;

        if (strlen($v) < $limit) {
            return $v;
        }

        return mb_substr($v, 0, $limit) . '...';
    }

    /**
     * @param LoggerEntry $logger_entry
     * @return string
     */
    protected function getUserNameWithLinkForEntry(LoggerEntry $logger_entry): string
    {
        $user_id = LoggerServiceProvider::getEntryService($this->container)->getUserIdForEntry($logger_entry);

        if (!$user_id) {
            if (is_null($logger_entry->getUserFullId())) {
                return '';
            }

            return $logger_entry->getUserFullId();
        }

        $user_service_container_name = LoggerConfig::getUserProfileRouteName();
        if ($user_service_container_name) {
            $user_service = $this->container->get($user_service_container_name);
            if ($user_service instanceof InterfaceEntityService) {
                $user_obj = $user_service->getById($user_id, false);
            }

            if (is_null($user_obj)) {
                return $logger_entry->getUserFullId();
            }

            if (!method_exists($user_obj, 'getName')) {
                return $logger_entry->getUserFullId();
            }
        }

        $user_profile_route_name = LoggerConfig::getUserProfileRouteName();
        if (!$user_profile_route_name) {
            return $logger_entry->getUserFullId();
        }

        return '<a href="' . $this->urlFor($user_profile_route_name, ['user_id' => $user_obj->getId()]) .'">' . $user_obj->getName() . '</a>';
    }

    /**
     * @param int $record_id
     * @return string
     */
    protected function renderRecordHead(int $record_id): string
    {
        $entry_obj = LoggerServiceProvider::getEntryService($this->container)->getById($record_id);

        $user_str = $this->getUserNameWithLinkForEntry($entry_obj);

        $html = '<dl class="dl-horizontal jumbotron" style="margin-top:20px;padding: 10px;">';
        $html .= '<dt style="padding: 5px 0;">Имя пользователя</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $user_str . '</dd>';
        $html .= '<dt style="padding: 5px 0;">Время изменения</dt>';
        $html .= '<dd style="padding: 5px 0;">' . date('d.m H:i', $entry_obj->getCreatedAtTs()) . '</dd>';
        $html .= '<dt style="padding: 5px 0;">IP адрес</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getUserIp() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">URL</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getRequestUriWithServerName() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">User agent</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getHttpUserAgent() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">Комментарий</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getComment() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">Идентификатор</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getObjectFullid() . '</dd>';
        $html .= '</dl>';

        return $html;
    }

    /**
     * @param int $record_id
     * @return string
     * @throws \ReflectionException
     */
    protected function renderObjectFields(int $record_id): string
    {
        $html = '<h2>Все поля объекта</h2>';

        $record_obj = LoggerServiceProvider::getEntryService($this->container)->getById($record_id);

        $record_objs = unserialize($record_obj->getSerializedObject());

        $value_as_list = $this->convertValueToList($record_objs);
        ksort($value_as_list);

        $last_path = '';

        foreach ($value_as_list as $path => $value) {
            $path_to_display = $path;

            if ($this->getPathWithoutLastElement($last_path) == $this->getPathWithoutLastElement($path)) {
                $elems = explode('.', $path);
                $last_elem = array_pop($elems);
                if (count($elems)) {
                    $path_to_display = '<span style="color: #999">' . implode('.', $elems) . '</span>.' . $last_elem;
                }
            }

            if (strlen($value) > 100) {
                $html .= '<div style="padding: 5px 0; border-bottom: 1px solid #ddd;">';

                $html .= '<div><b>' . $path_to_display . '</b></div>';
                $html .= '<div><pre style="white-space: pre-wrap;">' . Sanitize::sanitizeTagContent($value) . '</pre></div>';
                $html .= '</div>';
            } else {
                $html .= '<div style="padding: 5px 0; border-bottom: 1px solid #ddd;">';

                $html .= '<span style="padding-right: 50px;"><b>' . $path_to_display . '</b></span>';
                $html .= $value;
                $html .= '</div>';
            }


            $last_path = $path;
        }

        return $html;
    }

    /**
     * @param $value_value
     * @param string $value_path
     * @return array
     * @throws \ReflectionException
     */
    protected function convertValueToList($value_value, string $value_path = ''): array
    {
        if (is_null($value_value)) {
            return array($value_path => '#NULL#');
        }

        if (is_scalar($value_value)) {
            return array($value_path => $value_value);
        }

        $value_as_array = null;
        $output_array = [];

        if (is_array($value_value)) {
            $value_as_array = $value_value;
        }

        if (is_object($value_value)) {
            $value_as_array = [];

            foreach ($value_value as $property_name => $property_value) {
                $value_as_array[$property_name] = $property_value;
            }

            $reflect = new \ReflectionClass($value_value);
            $properties = $reflect->getProperties();

            foreach ($properties as $prop_obj) {
                // не показываем статические свойства класса - они не относятся к конкретному объекту
                // (например, это могут быть настройки CRUD для класса) и в журнале не нужны
                if ($prop_obj->isStatic()) {
                    continue;
                }

                $name = $prop_obj->getName();
                $value = $prop_obj->getValue($value_value);
                $value_as_array[$name] = $value;
            }
        }

        if (!is_array($value_as_array)) {
            throw new \Exception('Не удалось привести значение к массиву');
        }

        foreach ($value_as_array as $key => $value) {
            $key_path = $key;
            if ($value_path != '') {
                $key_path = $value_path . '.' . $key;
            }

            $value_output = $this->convertValueToList($value, $key_path);
            $output_array = array_merge($output_array, $value_output);
        }

        return $output_array;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getPathWithoutLastElement(string $path): string
    {
        $elems = explode('.', $path);
        array_pop($elems);

        return implode('.', $elems);
    }
}
