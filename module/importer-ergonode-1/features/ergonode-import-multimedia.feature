Feature: Ergonode import module

  Background:
    Given I am Authenticated as "test@ergonode.com"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"

  Scenario: Create Ergonode ZIP Source
    When I send a POST request to "/api/v1/en_GB/sources" with body:
      """
      {
        "type": "ergonode-zip",
        "name": "name",
        "import" : [
           "multimedia",
           "products"
        ]
      }
      """
    Then the response status code should be 201
    And store response param "id" as "source_id"

  Scenario: Upload Ergonode test import file
    When I send a POST request to "/api/v1/en_GB/sources/@source_id@/upload" with params:
      | key    | value                           |
      | upload | @ergonode-multimedia-import.zip |
    Then the response status code should be 201
    And the JSON node "id" should exist
    And store response param "id" as "import_id"

  Scenario: Get source import
    When I send a GET request to "/api/v1/en_GB/sources/@source_id@/imports/@import_id@"
    Then the response status code should be 200
    And the JSON nodes should be equal to:
      | id      | @import_id@ |
      | errors  | 0           |
      | status  | Ended       |
      | records | 4           |

  Scenario: Get source error import
    When I send a GET request to "/api/v1/en_GB/sources/@source_id@/imports/@import_id@/errors?view=list"
    Then the response status code should be 200

  Scenario: Get imported multimedia
    When I send a GET request to "/api/v1/en_GB/multimedia?filter=name=test.jpg&view=list"
    Then the response status code should be 200
    And the JSON node "collection[0].name" should contain "test.jpg"
    And store response param "collection[0].id" as "multimedia_1_id"

  Scenario: Get imported multimedia
    When I send a GET request to "/api/v1/en_GB/multimedia?filter=name=test-new.jpg&view=list"
    Then the response status code should be 200
    And the JSON node "collection[0].name" should contain "test-new.jpg"
    And store response param "collection[0].id" as "multimedia_2_id"

  Scenario: Get imported multimedia
    When I send a GET request to "/api/v1/en_GB/multimedia?filter=name=test.pdf&view=list"
    Then the response status code should be 200
    And the JSON node "collection[0].name" should contain "test.pdf"
    And store response param "collection[0].id" as "multimedia_3_id"

  Scenario: Get imported products with image attribute multimedia
    When I send a GET request to "/api/v1/en_GB/products?view=list&filter=esa_sku=SKU_multimedia&columns=image_attribute_local:en_GB,image_attribute_local:pl_PL"
    Then the response status code should be 200
    And the JSON node "collection[0].image_attribute_local:en_GB" should contain "@multimedia_1_id@"
    And the JSON node "collection[0].image_attribute_local:pl_PL" should contain "@multimedia_2_id@"

  Scenario: Get imported products with file gallery multimedia
    When I send a GET request to "/api/v1/en_GB/products?view=list&filter=esa_sku=SKU_multimedia&columns=gallery_attribute_local:en_GB,gallery_attribute_local:pl_PL"
    Then the response status code should be 200
    And the JSON node "collection[0].gallery_attribute_local:en_GB[0]" should contain "@multimedia_1_id@"
    And the JSON node "collection[0].gallery_attribute_local:pl_PL[0]" should contain "@multimedia_1_id@"
    And the JSON node "collection[0].gallery_attribute_local:pl_PL[1]" should contain "@multimedia_2_id@"

  Scenario: Get imported products with file attribute multimedia
    When I send a GET request to "/api/v1/en_GB/products?view=list&filter=esa_sku=SKU_multimedia&columns=file_attribute_local:en_GB,file_attribute_local:pl_PL"
    Then the response status code should be 200
    And the JSON node "collection[0].file_attribute_local:en_GB[0]" should contain "@multimedia_3_id@"
    And the JSON node "collection[0].file_attribute_local:pl_PL[0]" should contain "@multimedia_3_id@"
