Feature: Workflow

  Background:
    Given I am Authenticated as "test@ergonode.com"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"

  Scenario: Get roles
    When I send a GET request to "/api/v1/en_GB/roles"
    And store response param "collection[0].id" as "role_id"

  Scenario Outline: Create workflow status <id>
    And I send a "POST" request to "/api/v1/en_GB/status" with body:
      """
      {
        "color": "#ff0",
        "code": "ST @@random_md5@@"
      }
      """
    Then the response status code should be 201
    And store response param "id" as "<id>"
    Examples:
      | id                   |
      | workflow_status_1_id |
      | workflow_status_2_id |

  Scenario: Get workflow types
    When I send a "GET" request to "/api/v1/en_GB/dictionary/workflow-type"
    Then the response status code should be 200

  Scenario: Create workflow
    And I send a "POST" request to "/api/v1/en_GB/workflow" with body:
    """
      {
        "code": "WRK_@@random_code@@",
        "default_id": "@workflow_status_1_id@",
        "statuses": ["@workflow_status_1_id@"]
      }
    """
    Then the response status code should be 201
    And store response param "id" as "workflow_id"

  Scenario: Create workflow (wrong statuses)
    And I send a "POST" request to "/api/v1/en_GB/workflow" with body:
    """
    {
      "code": "WRK_@@random_code@@",
      "default_id": "@workflow_status_1_id@",
      "statuses": ["test"],
      "transitions": []
    }
    """
    Then the response status code should be 400

  Scenario: Create workflow (wrong default status)
    And I send a "POST" request to "/api/v1/en_GB/workflow" with body:
    """
    {
      "code": "WRK_@@random_code@@",
      "default_id": "default",
      "statuses": ["@workflow_status_1_id@"],
      "transitions": []
    }
    """
    Then the response status code should be 400

  Scenario: Get default workflow
    And I send a "GET" request to "/api/v1/en_GB/workflow/default"
    Then the response status code should be 200

  Scenario Outline: Update workflow
    And I send a "PUT" request to "/api/v1/en_GB/workflow/default" with body:
    """
    {
      "statuses": <statuses>,
      "default_id": "@workflow_status_1_id@",
      "transitions": [
      {
        "from": <from>,
        "to": <to>,
        "roles": <roles>
      }
      ]
    }
    """
    Then the response status code should be 200
    Examples:
      | statuses                                             | from                     | to                       | roles         |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | ["@role_id@"] |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | []            |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | ["@role_id@"] |

  Scenario Outline: Update workflow without default status
    And I send a "PUT" request to "/api/v1/en_GB/workflow/default" with body:
    """
    {
      "statuses": <statuses>,
      "transitions": [
      {
        "from": <from>,
        "to": <to>,
        "roles": <roles>
      }
      ]
    }
    """
    Then the response status code should be 200
    Examples:
      | statuses                                             | from                     | to                       | roles         |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | ["@role_id@"] |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | []            |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | ["@role_id@"] |


  Scenario Outline: Update workflow - validation error
    And I send a "PUT" request to "/api/v1/en_GB/workflow/default" with body:
    """
    {
      "statuses": <statuses>,
      "transitions": [
      {
        "from": <from>,
        "to": <to>,
        "roles": <roles>
      }
      ]
    }
    """
    Then the response status code should be 400
    Examples:
      | statuses                                             | from                     | to                       | roles               |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_1_id@" | ["@role_id@"]       |
      | ["@@random_uuid@@"]                                  | "@workflow_status_1_id@" | "@workflow_status_1_id@" | ["@role_id@"]       |
      | ["test"]                                             | "@workflow_status_1_id@" | "@workflow_status_1_id@" | ["@role_id@"]       |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@@random_uuid@@"        | "@workflow_status_1_id@" | ["@role_id@"]       |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "test"                   | "@workflow_status_1_id@" | ["@role_id@"]       |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@@random_uuid@@"        | ["@role_id@"]       |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "test"                   | ["@role_id@"]       |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | ["@@random_uuid@@"] |
      | ["@workflow_status_1_id@", "@workflow_status_2_id@"] | "@workflow_status_1_id@" | "@workflow_status_2_id@" | ["test"]            |

  Scenario: Delete workflow
    And I send a "DELETE" request to "/api/v1/en_GB/workflow/default"
    Then the response status code should be 204
